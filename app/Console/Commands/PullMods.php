<?php

namespace App\Console\Commands;

use DB;
use App\Mod;
use App\ModUser;
use Carbon\Carbon;
use Illuminate\Console\Command;
use SwgohHelp\Parsers\ModsParser;
use SwgohHelp\Parsers\ProfileParser;

use SwgohHelp\Enums\ModSet;
use SwgohHelp\Enums\ModSlot;
use SwgohHelp\Enums\UnitStat;

class PullMods extends Command
{
    use \App\Util\ParsesPlayers;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swgoh:mods {user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull a user‘s mods from swogh.help';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $arg = $this->argument('user');
        $this->line("Starting mod scrape for user [$arg]");
        $user = ModUser::firstOrNew(['name' => "$arg"]);

        if (config('services.shitty_bot.active')) {
            $this->info('Using swgoh.shittybots.me');
            $fetchId = empty($user->member->player_id) ? $user->name : $user->member->player_id;
            $this->info("Fetching with ${fetchId}");
            $profile = shitty_bot()->getPlayer($fetchId);
            $profile['updated'] = isset($profile['LastUpdated']) ? Carbon::createFromTimestamp($profile['LastUpdated'] / 1000) : Carbon::now();
        } else {
            $this->info('Using api.swgoh.help');
            $profile = swgoh()->getPlayer($user->name)
                ->map(function($json) {
                    $json['updated'] = Carbon::createFromTimestamp($json['updated']);

                    return $json;
                })
                ->first();
        }

        // if (!$user->hasChangesSince($profile['updated'])) {
        //     $this->line("Profile is up to date, returning");
        //     return 0;
        // }

        $user->last_scrape = new \DateTime;
        $user->save();

        $this->parseMember($profile, null, '🥯 ');

        $this->line("Mods pulled, returning");

        return 0;
    }
}
