<?php

namespace App\Console\Commands;

use DB;
use Redis;
use Storage;
use App\Mod;
use App\Zeta;
use App\Guild;
use App\Member;
use App\ModUser;
use App\Character;
use Carbon\Carbon;
use App\CharacterZeta;
use Illuminate\Console\Command;
use App\Util\API\GuildParser as ShittyParser;
use SwgohHelp\Parsers\GuildParser as SWGOHParser;

use SwgohHelp\Enums\ModSet;
use SwgohHelp\Enums\ModSlot;
use SwgohHelp\Enums\UnitStat;
use SwgohHelp\Enums\PlayerStatsIndex;


class PullGuild extends Command
{
    use \App\Util\ParsesPlayers;
    use \App\Util\UpdatesStats;
    use \App\Util\MetaChars;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swgoh:guild {--A|ally} {guild}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull all characters and member information for a guild from swgoh.help';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $isAllyCode = $this->option('ally');
        $start = Carbon::now();
        $guildID = $this->argument('guild');
        $isAllyCode = $isAllyCode || preg_match('/^\d{3}-?\d{3}-?\d{3}$/', $guildID);
        $guild = null;
        if ($isAllyCode) {
            $guildID = preg_replace('/[^0-9]/', '', $guildID);
            $member = Member::firstOrNew(['ally_code' => $guildID]);
            $guild = $member->guild;
        }

        if (is_null($guild) || !$guild->exists) {
            $guild = Guild::firstOrNew(['guild_id' => $guildID]);
        }
        $name = $guild->name ?? ($isAllyCode ? 'ALLY CODE ' : 'GUILD ') . $guildID;
        $this->info("Starting GuildParser for {$name}…");

        if (is_null($guild->id)) {
            Guild::withoutSyncingToSearch(function() use ($guild, $guildID, $name) {
                $guild->guild_id = $guildID;
                $guild->name = $name;
                $guild->url = 'not_scraped';
                $guild->gp = 0;
                $guild->save();
            });
        }


        if (config('services.shitty_bot.active')) {
            $this->info('Using swgoh.shittybots.me');
            $parser = new ShittyParser($guildID, $isAllyCode);
        } else {
            $this->info('Using api.swgoh.help');
            $parser = new SWGOHParser($guildID, $isAllyCode);
        }
        $this->info("Starting API pull…");

        $updated = [];
        do {
            if (count($updated)) {
                $updated = [];
            }

            $parser->scrape(function($member_data) use ($guild, &$updated) {
                $updated[] = $this->parseMember($member_data, $guild);
            }, /* pullMods: */ true);

            $time = Carbon::now()->diffInSeconds($start);
            $this->info("API pull finished, took {$time} seconds.");
            $updateCount = count($updated);
            $this->info("Updated {$updateCount} members, expecting to update {$parser->members()->count()}");
        } while (count($updated) < $parser->members()->count());

        $this->info("Saving basic info.");
        $guild->url = $parser->url();

        // make sure we didn't create a duplicate guild
        $possibleExistingGuild = Guild::where('name', $parser->name())->where('id', '<>', $guild->id)->first();

        if ($possibleExistingGuild) {
            if (strlen($guild->guild_id) === 9) {
                $this->info("Found existing guild, transitioning parsed members to $guild->name ($guild->id)");
                $possibleExistingGuild->members()->saveMany(
                    $guild->members
                );

                $guild->delete();

                $guild = $possibleExistingGuild;
            } else if (strlen($possibleExistingGuild->guild_id) === 9) {
                $this->info("Found existing guild with ally code, deleting it: $possibleExistingGuild->name ($possibleExistingGuild->id)");
                // No need to transition any members as all active members
                // have already been parsed and attached to $guild
                $possibleExistingGuild->delete();
            }
        }

        Guild::withoutSyncingToSearch(function() use ($guild, $parser) {
            $guild->name = $parser->name();
            $guild->gp = $parser->gp();
            $guild->icon = array_get($parser->data, 'bannerLogo', array_get($parser->data, 'bannerLogoId'));
            $guild->colors = array_get($parser->data, 'bannerColor', array_get($parser->data, 'bannerColorId'));
            $guild->save();
        });
        $this->info("Guild saved.");

        $guild->members()->searchable();

        $this->info("Guild Members’ search info updated");

        $publicStore = Storage::disk('public');

        if (!$publicStore->exists("$guild->icon.png")) {
            $this->info("Fetching icon from swgoh.gg");
            guzzle()->get("https://swgoh.gg/static/img/assets/tex.$guild->icon.png", [
                'sink' => storage_path("app/public/$guild->icon.png"),
            ]);
        }

        $removeCount = $guild->members()->whereNotIn('id', $updated)->count();
        $this->info("Removing all guild members not updated (${removeCount})");
        DB::transaction(function() use ($guild, $updated) {
            $guild->members()->whereNotIn('id', $updated)->each(function($member) {
                $member->guild()->dissociate();
                $member->save();
            });
        });
        $this->info("Updating guild GP totals");
        $guild->gp = $guild->members()->sum('gp');
        $guild->save();
        $this->info("Guild cleanup done.");

        $this->info("Updating member stats");

        $this->updateMemberStats($guild->members);

        $this->info("Member stats updated.");

        // Update the guild stats table
        $this->info("Updating guild stats");

        $unitSelects = static::getCompareCharacters()->keys()->reduce(function($query, $char) {
            return "$query
                sum((characters.unit_name = '{$char}') :: int) as {$char},
                sum((characters.unit_name = '{$char}' AND characters.gear_level = 11) :: int) as {$char}_11,
                sum((characters.unit_name = '{$char}' AND characters.gear_level = 12) :: int) as {$char}_12,
                sum((characters.unit_name = '{$char}' AND characters.gear_level = 13) :: int) as {$char}_13,
                sum((characters.unit_name = '{$char}' AND characters.relic >= 7) :: int) as {$char}_r_total,
                sum((characters.unit_name = '{$char}' AND characters.relic = 7) :: int) as {$char}_r5,
                sum((characters.unit_name = '{$char}' AND characters.relic = 8) :: int) as {$char}_r6,
                sum((characters.unit_name = '{$char}' AND characters.relic = 9) :: int) as {$char}_r7,
                sum((characters.unit_name = '{$char}' AND jsonb_array_length((data->'purchasedAbilityIdList')::jsonb) > 0) :: int) as {$char}_ultimate,
            ";
        }, '');

        $unitStatData = collect(DB::select("SELECT
                max(guilds.gp) as gp,
                sum((characters.gear_level = 13) :: int) as gear_13,
                sum((characters.gear_level = 12) :: int) as gear_12,
                sum((characters.gear_level = 11) :: int) as gear_11,
                sum((characters.relic = 9) :: int) as relic_7,
                sum((characters.relic = 8) :: int) as relic_6,
                sum((characters.relic = 7) :: int) as relic_5,
                $unitSelects
                count(distinct members.id) as member_count
            from guilds
            inner join members on members.guild_id = guilds.id
            inner join characters on characters.member_id = members.id
            inner join characters_raw on characters_raw.character_id = characters.id
            where guilds.id = ?
        ", [$guild->id]));

        $modStatData = collect(DB::select("SELECT
                sum((pips = 6)::int) as six_dot,
                sum((speed >= 10)::int) as ten_plus,
                sum((speed >= 15)::int) as fifteen_plus,
                sum((speed >= 20)::int) as twenty_plus,
                sum((speed >= 25)::int) as twenty_five_plus,
                sum((offense >= 100)::int) as one_hundred_offense,
                sum((offense >= 150)::int) as one_fifty_offense,
                sum((offense_percent >= 4)::int) as four_percent_offense
            from (
                select
                    mods.pips,
                    CASE
                        WHEN secondary_1_type = 'UNITSTATSPEED' THEN trim(trailing '%' from secondary_1_value)::numeric
                        WHEN secondary_2_type = 'UNITSTATSPEED' THEN trim(trailing '%' from secondary_2_value)::numeric
                        WHEN secondary_3_type = 'UNITSTATSPEED' THEN trim(trailing '%' from secondary_3_value)::numeric
                        WHEN secondary_4_type = 'UNITSTATSPEED' THEN trim(trailing '%' from secondary_4_value)::numeric
                    ELSE 0 END as speed,
                    CASE
                        WHEN secondary_1_type = 'UNITSTATOFFENSE' THEN trim(trailing '%' from secondary_1_value)::numeric
                        WHEN secondary_2_type = 'UNITSTATOFFENSE' THEN trim(trailing '%' from secondary_2_value)::numeric
                        WHEN secondary_3_type = 'UNITSTATOFFENSE' THEN trim(trailing '%' from secondary_3_value)::numeric
                        WHEN secondary_4_type = 'UNITSTATOFFENSE' THEN trim(trailing '%' from secondary_4_value)::numeric
                    ELSE 0 END as offense,
                    CASE
                        WHEN secondary_1_type = 'UNITSTATOFFENSEPERCENTADDITIVE' THEN trim(trailing '%' from secondary_1_value)::numeric
                        WHEN secondary_2_type = 'UNITSTATOFFENSEPERCENTADDITIVE' THEN trim(trailing '%' from secondary_2_value)::numeric
                        WHEN secondary_3_type = 'UNITSTATOFFENSEPERCENTADDITIVE' THEN trim(trailing '%' from secondary_3_value)::numeric
                        WHEN secondary_4_type = 'UNITSTATOFFENSEPERCENTADDITIVE' THEN trim(trailing '%' from secondary_4_value)::numeric
                    ELSE 0 END as offense_percent
                from mods
                inner join mod_users on mods.mod_user_id = mod_users.id
                inner join members on mod_users.name = members.ally_code
                inner join guilds on guilds.id = members.guild_id
                where guilds.id = ?
            ) mod_totals
        ", [$guild->id]));

        $guildStats = $guild->stats;
        $guildStats->unit_data = $unitStatData->first();
        $guildStats->mod_data = $modStatData->first();
        $guildStats->save();

        $this->info("Guild stats updated.");

        $time = Carbon::now()->diffInSeconds($start);
        $this->comment("Returning. Scrape took {$time} seconds.");
        return 0;
    }
}
