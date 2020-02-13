<?php

namespace App\Console\Commands;

use DB;
use Illuminate\Console\Command;

class RefreshViews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swgoh:refresh-views';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh materialized views';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info("Starting view refresh…");
        DB::statement("REFRESH MATERIALIZED VIEW guild_unit_counts");
        $this->line("guild_unit_counts done");
        DB::statement("REFRESH MATERIALIZED VIEW guild_mod_counts");
        $this->line("guild_mod_counts done");
        DB::statement("REFRESH MATERIALIZED VIEW member_stats");
        $this->line("member_stats done");
        $this->info("Done.");
    }
}
