<?php

namespace App\Console\Commands;

use DB;
use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RebuildViews extends Command
{
    use \App\Util\MetaChars;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swgoh:rebuild-views';

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
        $updates = static::getCompareCharacters()->keys()->reduce(function($query, $char) {
            return "$query
                sum((characters.unit_name = '{$char}') :: int) as {$char},
                sum((characters.unit_name = '{$char}' AND characters.gear_level = 11) :: int) as {$char}_11,
                sum((characters.unit_name = '{$char}' AND characters.gear_level = 12) :: int) as {$char}_12,
                sum((characters.unit_name = '{$char}' AND characters.gear_level = 13) :: int) as {$char}_13,
                sum((characters.unit_name = '{$char}' AND characters.relic >= 7) :: int) as {$char}_r_total,
                sum((characters.unit_name = '{$char}' AND characters.relic = 7) :: int) as {$char}_r5,
                sum((characters.unit_name = '{$char}' AND characters.relic = 8) :: int) as {$char}_r6,
                sum((characters.unit_name = '{$char}' AND characters.relic = 9) :: int) as {$char}_r7,
            ";
        }, '');

        $this->info("Dropping guild_unit_counts 🪓");
        DB::statement("DROP MATERIALIZED VIEW guild_unit_counts");
        $this->info("Re-creating guild_unit_counts 🧚🏻‍♀️");
        DB::statement("CREATE MATERIALIZED VIEW guild_unit_counts AS
            select
                guilds.guild_id,
                max(guilds.gp) as gp,
                sum((characters.gear_level = 13) :: int) as gear_13,
                sum((characters.gear_level = 12) :: int) as gear_12,
                sum((characters.gear_level = 11) :: int) as gear_11,
                sum((characters.relic = 9) :: int) as relic_7,
                sum((characters.relic = 8) :: int) as relic_6,
                sum((characters.relic = 7) :: int) as relic_5,

                $updates

                count(distinct members.id) as member_count
            from guilds
            inner join members on members.guild_id = guilds.id
            inner join characters on characters.member_id = members.id
            group by guilds.guild_id
        ");

        $this->info("Re-creating guild_unit_counts index 🧚‍♂️");
        Schema::table('guild_unit_counts', function(Blueprint $table) {
			$table->index('guild_id');
        });

        $this->info("Done.");
    }
}
