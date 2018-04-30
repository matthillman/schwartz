<?php

namespace App\Console\Commands;

use DB;
use App\Mod;
use App\ModUser;
use App\Mods\ModsParser;
use Illuminate\Console\Command;

class PullMods extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mods:pull {user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull a user‘s mods from swogh.gg';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $user = ModUser::firstOrNew(['name' => $this->argument('user')]);
        $user->last_scrape = new \DateTime;
        $parser = new ModsParser($user->name);
        $parser->scrape();

        DB::transaction(function() use ($user, $parser) {
            $user->save();

            $user->mods()->whereNotIn('uid', $parser->mods->pluck('uid'))->delete();

            $parser->mods->each(function($mod_data) use ($user) {
                $mod = Mod::firstOrNew(['uid' => $mod_data['uid']]);

                $mod->uid = $mod_data['uid'];
                $mod->slot = $mod_data['slot'];
                $mod->set = $mod_data['set'];
                $mod->pips = $mod_data['pips'];
                $mod->level = $mod_data['level'];
                $mod->name = $mod_data['name'];
                $mod->location = $mod_data['location'];

                $mod->primary = $mod_data['primary'];
                $mod->secondaries = $mod_data['secondaries'];

                $mod->user()->associate($user);
                $mod->save();
            });
        });

        return 0;
    }
}
