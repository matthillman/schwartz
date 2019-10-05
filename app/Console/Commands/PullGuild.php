<?php

namespace App\Console\Commands;

use DB;
use App\Mod;
use App\Zeta;
use App\Guild;
use App\Member;
use App\ModUser;
use App\Character;
use Carbon\Carbon;
use App\CharacterZeta;
use Illuminate\Console\Command;
use SwgohHelp\Parsers\GuildParser;

use SwgohHelp\Enums\ModSet;
use SwgohHelp\Enums\ModSlot;
use SwgohHelp\Enums\UnitStat;
use SwgohHelp\Enums\PlayerStatsIndex;


class PullGuild extends Command
{
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
    public function handle()
    {
        $isAllyCode = $this->option('ally');
        $start = Carbon::now();
        $guildID = $this->argument('guild');
        if ($isAllyCode) {
            $guildID = preg_replace('/[^0-9]/', '', $guildID);
            $member = Member::firstOrNew(['ally_code' => $guildID]);
            $guild = $member->guild;
        } else {
            $guild = Guild::firstOrNew(['guild_id' => $guildID]);
        }
        $name = $guild->name ?? ($isAllyCode ? 'ALLY CODE ' : 'GUILD ') . $guildID;
        $this->info("Starting GuildParser for {$name}…");

        if (is_null($guild->id)) {
            $guild->guild_id = $guildID;
            $guild->name = $name;
            $guild->url = 'not_scraped';
            $guild->gp = 0;
            $guild->save();
        }

        $parser = new GuildParser($guildID, $isAllyCode);
        $this->info("Starting API pull…");

        $updated = [];

        $zetaList = Zeta::all();
        $parser->scrape(function($member_data) use ($guild, $zetaList, &$updated) {
            $member = Member::firstOrNew(['ally_code' => (string)$member_data['allyCode']]);

            $member_data = stats()->addStatsTo([$member_data])->first();

            $ally = $member_data['allyCode'];
            $member->url = "/p/{$ally}/characters/";
            $member->player = $member_data['name'];

            $stats = collect($member_data['stats']);

            $member->gp = $stats->where('index', PlayerStatsIndex::gp)->pluck('value')->first();
            $member->character_gp = $stats->where('index', PlayerStatsIndex::charGP)->pluck('value')->first();
            $member->ship_gp = $stats->where('index', PlayerStatsIndex::shipGP)->pluck('value')->first();

            $member->guild()->associate($guild);
            $member->save();

            $modUser = ModUser::firstOrNew(['name' => (string)$ally]);
            $modUser->last_scrape = new \DateTime;
            $modUser->save();

            $updated[] = $member->id;

            $this->comment("   Looping over units for {$member->player}…");
            $roster = collect($member_data['roster']);
            $mappedRoster = $roster->map(function($unit) use ($member, $modUser) {
                $isChar = $unit['combatType'] === 1;
                $character = [
                    'member_id' => $member->id,
                    'unit_name' => $unit['defId'],
                    'gear_level' => $unit['gear'],
                    'power' => $unit['gp'],
                    'level' => $unit['level'],
                    'combat_type' => $unit['combatType'],
                    'rarity' => $unit['rarity'],
                    'relic' => $isChar ? $unit['relic']['currentTier'] : 0,
                    'stats' => $unit['stats'],
                ];
                $mods = collect($unit['mods'] ?? [])->map(function($mod) use ($character, $modUser) {
                    $modItem = [
                        "uid" => $mod["id"],
                        'slot' => (new ModSlot(+$mod['slot']))->getKey(),
                        'set' => (new ModSet(+$mod['set']))->getKey(),
                        "pips" => $mod["pips"],
                        "level" => $mod["level"],
                        "name" => "",
                        "location" => $character['unit_name'],
                        "mod_user_id" => $modUser->id,
                        "tier" => $mod["tier"],
                        "primary_type" => (new UnitStat($mod["primaryStat"]["unitStat"]))->getKey(),
                        "primary_value" => $mod["primaryStat"]["value"],
                    ];


                    collect([1, 2, 3, 4])->each(function($index) use ($mod, &$modItem) {
                        $statIndex = $index - 1;
                        $secondaryType = array_get($mod, "secondaryStat.${statIndex}.unitStat", null);
                        $secondaryType = $secondaryType !== null ? (new UnitStat($secondaryType))->getKey() : null;
                        $modItem["secondary_${index}_type"] = $secondaryType;
                        $modItem["secondary_${index}_value"] = array_get($mod, "secondaryStat.${statIndex}.value", null);
                    });

                    return $modItem;
                });

                return ['char' => $character, 'mods' => $mods];
            });

            $chars = $mappedRoster->pluck('char');
            $mods = $mappedRoster->pluck('mods')->flatten(1);

            $cCount = $chars->count();
            $this->info("   ➡ Doing the character insert (${cCount} rows)");
            Character::upsert($chars->toArray(), "(member_id, unit_name)");
            $this->info("   ⬅ Done with character insert.");

            $skills = $roster->pluck('skills')->flatten(1)->where('isZeta', true)->where('tier', 8)->pluck('id');
            $memberChars = $member->characters()->get();
            $zetas = $zetaList->whereIn('skill_id', $skills)->map(function($zeta) use ($memberChars) {
                $character = $memberChars->where('unit_name', $zeta->character_id)->first();
                return [
                    'zeta_id' => $zeta->id,
                    'character_id' => $character->id,
                ];
            });

            $zCount = $zetas->count();
            if ($zCount > 0) {
                $this->info("   ➡ Doing the zeta insert (${zCount} rows)");
                CharacterZeta::upsert($zetas->toArray(), "(character_id, zeta_id)");
                $this->info("   ⬅ Done with zeta insert.");

                $existing = $member->characters->map(function($c) { return $c->zetas; })->collapse()->pluck('id');
                $diff = $existing->diff($zetas->pluck('zeta_id'))->values();
                $zCount = $diff->count();
                if ($zCount > 0) {
                    $this->info("   ➡ Deleting extra zetas (${zCount} rows)");
                    CharacterZeta::whereIn('zeta_id', $diff)->delete();
                    $this->info("   ⬅ Extra zetas deleted.");
                }
            } else {
                $this->info("   No zetas to insert.");
            }

            $mCount = $mods->count();
            $this->info("   ➡ Doing the mod insert for {$modUser->name} (${mCount} rows)");
            Mod::upsert($mods->toArray(), "(uid)");
            $this->info("   ⬅ Done with mod insert.");

            $this->comment("   {$member->player} done.");
        }, /* pullMods: */ true);

        $this->info("API pull finished.");

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

        $guild->name = $parser->name();
        $guild->gp = $parser->gp();
        $guild->save();
        $this->info("Guild saved.");

        $removeCount = $guild->members()->whereNotIn('id', $updated)->count();
        $this->info("Removing all guild members not updated (${removeCount})");
        DB::transaction(function() use ($guild, $updated) {
            $guild->members()->whereNotIn('id', $updated)->each(function($member) {
                $member->guild()->dissociate();
                $member->save();
            });
        });
        $this->info("Guild cleanup done.");

        $time = Carbon::now()->diffInSeconds($start);
        $this->comment("Returning. Scrape took {$time} seconds.");
        return 0;
    }
}
