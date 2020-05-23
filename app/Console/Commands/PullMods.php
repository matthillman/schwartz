<?php

namespace App\Console\Commands;

use DB;
use App\Mod;
use App\Member;
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
    use \App\Util\UpdatesStats;

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
    protected $description = 'Pull a user‘s profile';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $arg = $this->argument('user');
        $this->line("Starting scrape for user [$arg]");

        if (config('services.shitty_bot.active')) {
            $this->info('Using swgoh.shittybots.me');

            $isAllyCode = preg_match('/^\d{3}-?\d{3}-?\d{3}$/', $arg);
            if (!$isAllyCode) {
                $this->info("Input was not an ally code, trying it as a player ID");
                $fetchId = $arg;
            } else {
                $arg = str_replace('-', '', $arg);
                $user = ModUser::firstOrNew(['name' => "$arg"]);
                $fetchId = empty($user->member->player_id) ? $user->name : $user->member->player_id;
            }

            $this->info("Fetching with ${fetchId}");
            $profile = shitty_bot()->getPlayer($fetchId);
            $profile['updated'] = isset($profile['LastUpdated']) ? Carbon::createFromTimestamp($profile['LastUpdated'] / 1000) : Carbon::now();

            $user = ModUser::firstOrNew(['name' => $profile['allyCode']]);
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

        $memberId = $this->parseMember($profile, null, '🥯 ');
        $member = Member::find($memberId);

        $member->searchable();

        $this->info("Updating member stats");

        $this->updateMemberStats($member);

        $this->info("Member stats updated.");

        $this->line("Mods pulled, returning");

        return 0;
    }
}
