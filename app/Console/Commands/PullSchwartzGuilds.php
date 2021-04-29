<?php

namespace App\Console\Commands;

use App\Guild;
use App\Jobs\ProcessGuild;
use Illuminate\Console\Command;

class PullSchwartzGuilds extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swgoh:schwartz';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull all characters and member information for all schwartz guilds';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Guild::where('schwartz', 'true')->each(function($guild) {
            ProcessGuild::dispatch($guild->guild_id);
        });
    }
}