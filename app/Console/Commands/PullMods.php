<?php

namespace App\Console\Commands;

use DB;
use App\Mod;
use App\ModUser;
use SwgohHelp\Parsers\ModsParser;
use SwgohHelp\Parsers\ProfileParser;
use Illuminate\Console\Command;

class PullMods extends Command
{
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
        \Log::info("Starting mod scrape for user", [$arg]);
        $user = ModUser::firstOrNew(['name' => "$arg"]);

        $profile = new ProfileParser($user->name);
        $profile->scrape();

        if (!$user->hasChangesSince($profile->updated())) {
            \Log::info("Profile is up to date, returning");
            return 0;
        }

        $user->last_scrape = new \DateTime;
        $parser = new ModsParser($user->name);
        $parser->scrape();

        DB::transaction(function() use ($user, $parser) {
            $user->save();

            $user->mods()->whereNotIn('uid', $parser->getMods()->pluck('uid'))->delete();

            $parser->getMods()->each(function($mod_data) use ($user) {
                $mod = Mod::firstOrNew(['uid' => $mod_data['uid']]);

                $mod->uid = $mod_data['uid'];
                $mod->slot = $mod_data['slot'];
                $mod->set = $mod_data['set'];
                $mod->pips = $mod_data['pips'];
                $mod->level = $mod_data['level'];
                $mod->name = $mod_data['name'];
                $mod->location = $mod_data['location'];
                $mod->tier = $mod_data['tier'];

                $mod->primary = $mod_data['primary'];
                $mod->secondaries = $mod_data['secondaries'];

                $mod->user()->associate($user);
                $mod->save();
            });
        });

        \Log::info("Mods pulled, returning");

        return 0;
    }
}
