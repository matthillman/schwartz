<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixGuildUnitCounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("DROP MATERIALIZED VIEW guild_unit_counts");
        DB::statement("CREATE MATERIALIZED VIEW guild_unit_counts AS
            select
                guilds.guild_id,
                max(guilds.gp) as gp,
                count(distinct members.id) as member_count,
                sum((characters.gear_level = 13) :: int) as gear_13,
                sum((characters.gear_level = 12) :: int) as gear_12,
                sum((characters.gear_level = 11) :: int) as gear_11,
                sum((characters.relic = 9) :: int) as relic_7,
                sum((characters.relic = 8) :: int) as relic_6,
                sum((characters.relic = 7) :: int) as relic_5,
                sum((characters.unit_name = 'GENERALSKYWALKER') :: int) as GENERALSKYWALKER,
                sum((characters.unit_name = 'GENERALSKYWALKER' AND characters.gear_level = 11) :: int) as GENERALSKYWALKER_11,
                sum((characters.unit_name = 'GENERALSKYWALKER' AND characters.gear_level = 12) :: int) as GENERALSKYWALKER_12,
                sum((characters.unit_name = 'GENERALSKYWALKER' AND characters.gear_level = 13) :: int) as GENERALSKYWALKER_13,
                sum((characters.unit_name = 'GENERALSKYWALKER' AND characters.relic >= 7) :: int) as GENERALSKYWALKER_r_total,
                sum((characters.unit_name = 'GENERALSKYWALKER' AND characters.relic = 7) :: int) as GENERALSKYWALKER_r5,
                sum((characters.unit_name = 'GENERALSKYWALKER' AND characters.relic = 8) :: int) as GENERALSKYWALKER_r6,
                sum((characters.unit_name = 'GENERALSKYWALKER' AND characters.relic = 9) :: int) as GENERALSKYWALKER_r7,
                sum((characters.unit_name = 'DARTHREVAN') :: int) as DARTHREVAN,
                sum((characters.unit_name = 'DARTHREVAN' AND characters.gear_level = 11) :: int) as DARTHREVAN_11,
                sum((characters.unit_name = 'DARTHREVAN' AND characters.gear_level = 12) :: int) as DARTHREVAN_12,
                sum((characters.unit_name = 'DARTHREVAN' AND characters.gear_level = 13) :: int) as DARTHREVAN_13,
                sum((characters.unit_name = 'DARTHREVAN' AND characters.relic >= 7) :: int) as DARTHREVAN_r_total,
                sum((characters.unit_name = 'DARTHREVAN' AND characters.relic = 7) :: int) as DARTHREVAN_r5,
                sum((characters.unit_name = 'DARTHREVAN' AND characters.relic = 8) :: int) as DARTHREVAN_r6,
                sum((characters.unit_name = 'DARTHREVAN' AND characters.relic = 9) :: int) as DARTHREVAN_r7,
                sum((characters.unit_name = 'DARTHMALAK') :: int) as DARTHMALAK,
                sum((characters.unit_name = 'DARTHMALAK' AND characters.gear_level = 11) :: int) as DARTHMALAK_11,
                sum((characters.unit_name = 'DARTHMALAK' AND characters.gear_level = 12) :: int) as DARTHMALAK_12,
                sum((characters.unit_name = 'DARTHMALAK' AND characters.gear_level = 13) :: int) as DARTHMALAK_13,
                sum((characters.unit_name = 'DARTHMALAK' AND characters.relic >= 7) :: int) as DARTHMALAK_r_total,
                sum((characters.unit_name = 'DARTHMALAK' AND characters.relic = 7) :: int) as DARTHMALAK_r5,
                sum((characters.unit_name = 'DARTHMALAK' AND characters.relic = 8) :: int) as DARTHMALAK_r6,
                sum((characters.unit_name = 'DARTHMALAK' AND characters.relic = 9) :: int) as DARTHMALAK_r7,
                sum((characters.unit_name = 'JEDIKNIGHTREVAN') :: int) as JEDIKNIGHTREVAN,
                sum((characters.unit_name = 'JEDIKNIGHTREVAN' AND characters.gear_level = 11) :: int) as JEDIKNIGHTREVAN_11,
                sum((characters.unit_name = 'JEDIKNIGHTREVAN' AND characters.gear_level = 12) :: int) as JEDIKNIGHTREVAN_12,
                sum((characters.unit_name = 'JEDIKNIGHTREVAN' AND characters.gear_level = 13) :: int) as JEDIKNIGHTREVAN_13,
                sum((characters.unit_name = 'JEDIKNIGHTREVAN' AND characters.relic >= 7) :: int) as JEDIKNIGHTREVAN_r_total,
                sum((characters.unit_name = 'JEDIKNIGHTREVAN' AND characters.relic = 7) :: int) as JEDIKNIGHTREVAN_r5,
                sum((characters.unit_name = 'JEDIKNIGHTREVAN' AND characters.relic = 8) :: int) as JEDIKNIGHTREVAN_r6,
                sum((characters.unit_name = 'JEDIKNIGHTREVAN' AND characters.relic = 9) :: int) as JEDIKNIGHTREVAN_r7,
                sum((characters.unit_name = 'PADMEAMIDALA') :: int) as PADMEAMIDALA,
                sum((characters.unit_name = 'PADMEAMIDALA' AND characters.gear_level = 11) :: int) as PADMEAMIDALA_11,
                sum((characters.unit_name = 'PADMEAMIDALA' AND characters.gear_level = 12) :: int) as PADMEAMIDALA_12,
                sum((characters.unit_name = 'PADMEAMIDALA' AND characters.gear_level = 13) :: int) as PADMEAMIDALA_13,
                sum((characters.unit_name = 'PADMEAMIDALA' AND characters.relic >= 7) :: int) as PADMEAMIDALA_r_total,
                sum((characters.unit_name = 'PADMEAMIDALA' AND characters.relic = 7) :: int) as PADMEAMIDALA_r5,
                sum((characters.unit_name = 'PADMEAMIDALA' AND characters.relic = 8) :: int) as PADMEAMIDALA_r6,
                sum((characters.unit_name = 'PADMEAMIDALA' AND characters.relic = 9) :: int) as PADMEAMIDALA_r7,
                sum((characters.unit_name = 'GRIEVOUS') :: int) as GRIEVOUS,
                sum((characters.unit_name = 'GRIEVOUS' AND characters.gear_level = 11) :: int) as GRIEVOUS_11,
                sum((characters.unit_name = 'GRIEVOUS' AND characters.gear_level = 12) :: int) as GRIEVOUS_12,
                sum((characters.unit_name = 'GRIEVOUS' AND characters.gear_level = 13) :: int) as GRIEVOUS_13,
                sum((characters.unit_name = 'GRIEVOUS' AND characters.relic >= 7) :: int) as GRIEVOUS_r_total,
                sum((characters.unit_name = 'GRIEVOUS' AND characters.relic = 7) :: int) as GRIEVOUS_r5,
                sum((characters.unit_name = 'GRIEVOUS' AND characters.relic = 8) :: int) as GRIEVOUS_r6,
                sum((characters.unit_name = 'GRIEVOUS' AND characters.relic = 9) :: int) as GRIEVOUS_r7,
                sum((characters.unit_name = 'GEONOSIANBROODALPHA') :: int) as GEONOSIANBROODALPHA,
                sum((characters.unit_name = 'GEONOSIANBROODALPHA' AND characters.gear_level = 11) :: int) as GEONOSIANBROODALPHA_11,
                sum((characters.unit_name = 'GEONOSIANBROODALPHA' AND characters.gear_level = 12) :: int) as GEONOSIANBROODALPHA_12,
                sum((characters.unit_name = 'GEONOSIANBROODALPHA' AND characters.gear_level = 13) :: int) as GEONOSIANBROODALPHA_13,
                sum((characters.unit_name = 'GEONOSIANBROODALPHA' AND characters.relic >= 7) :: int) as GEONOSIANBROODALPHA_r_total,
                sum((characters.unit_name = 'GEONOSIANBROODALPHA' AND characters.relic = 7) :: int) as GEONOSIANBROODALPHA_r5,
                sum((characters.unit_name = 'GEONOSIANBROODALPHA' AND characters.relic = 8) :: int) as GEONOSIANBROODALPHA_r6,
                sum((characters.unit_name = 'GEONOSIANBROODALPHA' AND characters.relic = 9) :: int) as GEONOSIANBROODALPHA_r7,
                sum((characters.unit_name = 'DARTHTRAYA') :: int) as DARTHTRAYA,
                sum((characters.unit_name = 'DARTHTRAYA' AND characters.gear_level = 11) :: int) as DARTHTRAYA_11,
                sum((characters.unit_name = 'DARTHTRAYA' AND characters.gear_level = 12) :: int) as DARTHTRAYA_12,
                sum((characters.unit_name = 'DARTHTRAYA' AND characters.gear_level = 13) :: int) as DARTHTRAYA_13,
                sum((characters.unit_name = 'DARTHTRAYA' AND characters.relic >= 7) :: int) as DARTHTRAYA_r_total,
                sum((characters.unit_name = 'DARTHTRAYA' AND characters.relic = 7) :: int) as DARTHTRAYA_r5,
                sum((characters.unit_name = 'DARTHTRAYA' AND characters.relic = 8) :: int) as DARTHTRAYA_r6,
                sum((characters.unit_name = 'DARTHTRAYA' AND characters.relic = 9) :: int) as DARTHTRAYA_r7,
                sum((characters.unit_name = 'ANAKINKNIGHT') :: int) as ANAKINKNIGHT,
                sum((characters.unit_name = 'ANAKINKNIGHT' AND characters.gear_level = 11) :: int) as ANAKINKNIGHT_11,
                sum((characters.unit_name = 'ANAKINKNIGHT' AND characters.gear_level = 12) :: int) as ANAKINKNIGHT_12,
                sum((characters.unit_name = 'ANAKINKNIGHT' AND characters.gear_level = 13) :: int) as ANAKINKNIGHT_13,
                sum((characters.unit_name = 'ANAKINKNIGHT' AND characters.relic >= 7) :: int) as ANAKINKNIGHT_r_total,
                sum((characters.unit_name = 'ANAKINKNIGHT' AND characters.relic = 7) :: int) as ANAKINKNIGHT_r5,
                sum((characters.unit_name = 'ANAKINKNIGHT' AND characters.relic = 8) :: int) as ANAKINKNIGHT_r6,
                sum((characters.unit_name = 'ANAKINKNIGHT' AND characters.relic = 9) :: int) as ANAKINKNIGHT_r7
            from guilds
            inner join members on members.guild_id = guilds.id
            inner join characters on characters.member_id = members.id
            group by guilds.guild_id
        ");

        Schema::table('guild_unit_counts', function(Blueprint $table) {
			$table->index('guild_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DROP MATERIALIZED VIEW guild_mod_counts");
        DB::statement("DROP MATERIALIZED VIEW guild_unit_counts");
        Schema::table('members', function(Blueprint $table) {
			$table->dropIndex('members_guild_id_index');
        });
    }
}
