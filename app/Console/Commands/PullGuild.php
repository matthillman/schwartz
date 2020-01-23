<?php

namespace App\Console\Commands;

use DB;
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
            $guild->guild_id = $guildID;
            $guild->name = $name;
            $guild->url = 'not_scraped';
            $guild->gp = 0;
            $guild->save();
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

        $guild->name = $parser->name();
        $guild->gp = $parser->gp();
        $guild->icon = array_get($parser->data, 'bannerLogo', array_get($parser->data, 'bannerLogoId'));
        $guild->colors = array_get($parser->data, 'bannerColor', array_get($parser->data, 'bannerColorId'));
        $guild->save();
        $this->info("Guild saved.");

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

        $time = Carbon::now()->diffInSeconds($start);
        $this->comment("Returning. Scrape took {$time} seconds.");
        return 0;
    }
}
