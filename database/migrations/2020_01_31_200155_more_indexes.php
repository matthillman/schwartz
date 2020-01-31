<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MoreIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::table('members', function(Blueprint $table) {
		// 	$table->index('guild_id');
        // });

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
                sum((characters.unit_name = 'GENERALSKYWALKER'AND characters.relic > 5) :: int) as GENERALSKYWALKER_r_total,
                sum((characters.unit_name = 'GENERALSKYWALKER'AND characters.relic = 7) :: int) as GENERALSKYWALKER_r5,
                sum((characters.unit_name = 'GENERALSKYWALKER'AND characters.relic = 8) :: int) as GENERALSKYWALKER_r6,
                sum((characters.unit_name = 'GENERALSKYWALKER'AND characters.relic = 9) :: int) as GENERALSKYWALKER_r7,
                sum((characters.unit_name = 'DARTHREVAN') :: int) as DARTHREVAN,
                sum((characters.unit_name = 'DARTHREVAN'AND characters.gear_level = 11) :: int) as DARTHREVAN_11,
                sum((characters.unit_name = 'DARTHREVAN'AND characters.gear_level = 12) :: int) as DARTHREVAN_12,
                sum((characters.unit_name = 'DARTHREVAN'AND characters.gear_level = 13) :: int) as DARTHREVAN_13,
                sum((characters.unit_name = 'DARTHREVAN'AND characters.relic > 5) :: int) as DARTHREVAN_r_total,
                sum((characters.unit_name = 'DARTHREVAN'AND characters.relic = 7) :: int) as DARTHREVAN_r5,
                sum((characters.unit_name = 'DARTHREVAN'AND characters.relic = 8) :: int) as DARTHREVAN_r6,
                sum((characters.unit_name = 'DARTHREVAN'AND characters.relic = 9) :: int) as DARTHREVAN_r7,
                sum((characters.unit_name = 'DARTHMALAK') :: int) as DARTHMALAK,
                sum((characters.unit_name = 'DARTHMALAK'AND characters.gear_level = 11) :: int) as DARTHMALAK_11,
                sum((characters.unit_name = 'DARTHMALAK'AND characters.gear_level = 12) :: int) as DARTHMALAK_12,
                sum((characters.unit_name = 'DARTHMALAK'AND characters.gear_level = 13) :: int) as DARTHMALAK_13,
                sum((characters.unit_name = 'DARTHMALAK'AND characters.relic > 5) :: int) as DARTHMALAK_r_total,
                sum((characters.unit_name = 'DARTHMALAK'AND characters.relic = 7) :: int) as DARTHMALAK_r5,
                sum((characters.unit_name = 'DARTHMALAK'AND characters.relic = 8) :: int) as DARTHMALAK_r6,
                sum((characters.unit_name = 'DARTHMALAK'AND characters.relic = 9) :: int) as DARTHMALAK_r7,
                sum((characters.unit_name = 'JEDIKNIGHTREVAN') :: int) as JEDIKNIGHTREVAN,
                sum((characters.unit_name = 'JEDIKNIGHTREVAN'AND characters.gear_level = 11) :: int) as JEDIKNIGHTREVAN_11,
                sum((characters.unit_name = 'JEDIKNIGHTREVAN'AND characters.gear_level = 12) :: int) as JEDIKNIGHTREVAN_12,
                sum((characters.unit_name = 'JEDIKNIGHTREVAN'AND characters.gear_level = 13) :: int) as JEDIKNIGHTREVAN_13,
                sum((characters.unit_name = 'JEDIKNIGHTREVAN'AND characters.relic > 5) :: int) as JEDIKNIGHTREVAN_r_total,
                sum((characters.unit_name = 'JEDIKNIGHTREVAN'AND characters.relic = 7) :: int) as JEDIKNIGHTREVAN_r5,
                sum((characters.unit_name = 'JEDIKNIGHTREVAN'AND characters.relic = 8) :: int) as JEDIKNIGHTREVAN_r6,
                sum((characters.unit_name = 'JEDIKNIGHTREVAN'AND characters.relic = 9) :: int) as JEDIKNIGHTREVAN_r7,
                sum((characters.unit_name = 'PADMEAMIDALA') :: int) as PADMEAMIDALA,
                sum((characters.unit_name = 'PADMEAMIDALA'AND characters.gear_level = 11) :: int) as PADMEAMIDALA_11,
                sum((characters.unit_name = 'PADMEAMIDALA'AND characters.gear_level = 12) :: int) as PADMEAMIDALA_12,
                sum((characters.unit_name = 'PADMEAMIDALA'AND characters.gear_level = 13) :: int) as PADMEAMIDALA_13,
                sum((characters.unit_name = 'PADMEAMIDALA'AND characters.relic > 5) :: int) as PADMEAMIDALA_r_total,
                sum((characters.unit_name = 'PADMEAMIDALA'AND characters.relic = 7) :: int) as PADMEAMIDALA_r5,
                sum((characters.unit_name = 'PADMEAMIDALA'AND characters.relic = 8) :: int) as PADMEAMIDALA_r6,
                sum((characters.unit_name = 'PADMEAMIDALA'AND characters.relic = 9) :: int) as PADMEAMIDALA_r7,
                sum((characters.unit_name = 'GRIEVOUS') :: int) as GRIEVOUS,
                sum((characters.unit_name = 'GRIEVOUS'AND characters.gear_level = 11) :: int) as GRIEVOUS_11,
                sum((characters.unit_name = 'GRIEVOUS'AND characters.gear_level = 12) :: int) as GRIEVOUS_12,
                sum((characters.unit_name = 'GRIEVOUS'AND characters.gear_level = 13) :: int) as GRIEVOUS_13,
                sum((characters.unit_name = 'GRIEVOUS'AND characters.relic > 5) :: int) as GRIEVOUS_r_total,
                sum((characters.unit_name = 'GRIEVOUS'AND characters.relic = 7) :: int) as GRIEVOUS_r5,
                sum((characters.unit_name = 'GRIEVOUS'AND characters.relic = 8) :: int) as GRIEVOUS_r6,
                sum((characters.unit_name = 'GRIEVOUS'AND characters.relic = 9) :: int) as GRIEVOUS_r7,
                sum((characters.unit_name = 'GEONOSIANBROODALPHA') :: int) as GEONOSIANBROODALPHA,
                sum((characters.unit_name = 'GEONOSIANBROODALPHA'AND characters.gear_level = 11) :: int) as GEONOSIANBROODALPHA_11,
                sum((characters.unit_name = 'GEONOSIANBROODALPHA'AND characters.gear_level = 12) :: int) as GEONOSIANBROODALPHA_12,
                sum((characters.unit_name = 'GEONOSIANBROODALPHA'AND characters.gear_level = 13) :: int) as GEONOSIANBROODALPHA_13,
                sum((characters.unit_name = 'GEONOSIANBROODALPHA'AND characters.relic > 5) :: int) as GEONOSIANBROODALPHA_r_total,
                sum((characters.unit_name = 'GEONOSIANBROODALPHA'AND characters.relic = 7) :: int) as GEONOSIANBROODALPHA_r5,
                sum((characters.unit_name = 'GEONOSIANBROODALPHA'AND characters.relic = 8) :: int) as GEONOSIANBROODALPHA_r6,
                sum((characters.unit_name = 'GEONOSIANBROODALPHA'AND characters.relic = 9) :: int) as GEONOSIANBROODALPHA_r7,
                sum((characters.unit_name = 'DARTHTRAYA') :: int) as DARTHTRAYA,
                sum((characters.unit_name = 'DARTHTRAYA'AND characters.gear_level = 11) :: int) as DARTHTRAYA_11,
                sum((characters.unit_name = 'DARTHTRAYA'AND characters.gear_level = 12) :: int) as DARTHTRAYA_12,
                sum((characters.unit_name = 'DARTHTRAYA'AND characters.gear_level = 13) :: int) as DARTHTRAYA_13,
                sum((characters.unit_name = 'DARTHTRAYA'AND characters.relic > 5) :: int) as DARTHTRAYA_r_total,
                sum((characters.unit_name = 'DARTHTRAYA'AND characters.relic = 7) :: int) as DARTHTRAYA_r5,
                sum((characters.unit_name = 'DARTHTRAYA'AND characters.relic = 8) :: int) as DARTHTRAYA_r6,
                sum((characters.unit_name = 'DARTHTRAYA'AND characters.relic = 9) :: int) as DARTHTRAYA_r7,
                sum((characters.unit_name = 'ANAKINKNIGHT') :: int) as ANAKINKNIGHT,
                sum((characters.unit_name = 'ANAKINKNIGHT'AND characters.gear_level = 11) :: int) as ANAKINKNIGHT_11,
                sum((characters.unit_name = 'ANAKINKNIGHT'AND characters.gear_level = 12) :: int) as ANAKINKNIGHT_12,
                sum((characters.unit_name = 'ANAKINKNIGHT'AND characters.gear_level = 13) :: int) as ANAKINKNIGHT_13,
                sum((characters.unit_name = 'ANAKINKNIGHT'AND characters.relic > 5) :: int) as ANAKINKNIGHT_r_total,
                sum((characters.unit_name = 'ANAKINKNIGHT'AND characters.relic = 7) :: int) as ANAKINKNIGHT_r5,
                sum((characters.unit_name = 'ANAKINKNIGHT'AND characters.relic = 8) :: int) as ANAKINKNIGHT_r6,
                sum((characters.unit_name = 'ANAKINKNIGHT'AND characters.relic = 9) :: int) as ANAKINKNIGHT_r7
            from guilds
            inner join members on members.guild_id = guilds.id
            inner join characters on characters.member_id = members.id
            group by guilds.guild_id
        ");

        DB::statement("CREATE MATERIALIZED VIEW guild_mod_counts AS
            select
                guild_id,
                sum((pips = 6)::int) as six_dot,
                sum((speed >= 10)::int) as ten_plus,
                sum((speed >= 15)::int) as fifteen_plus,
                sum((speed >= 20)::int) as twenty_plus,
                sum((speed >= 25)::int) as twenty_five_plus,
                sum((offense >= 100)::int) as one_hundred_offense,
                sum((offense >= 150)::int) as one_fifty_offense,
                sum((offense_percent >= 4)::int) as four_percent_offense
            from (
                select
                    guilds.guild_id,
                    mods.pips,
                    CASE
                        WHEN secondary_1_type = 'UNITSTATSPEED' THEN trim(trailing '%' from secondary_1_value)::numeric
                        WHEN secondary_2_type = 'UNITSTATSPEED' THEN trim(trailing '%' from secondary_2_value)::numeric
                        WHEN secondary_3_type = 'UNITSTATSPEED' THEN trim(trailing '%' from secondary_3_value)::numeric
                        WHEN secondary_4_type = 'UNITSTATSPEED' THEN trim(trailing '%' from secondary_4_value)::numeric
                    ELSE 0 END as speed,
                    CASE
                        WHEN secondary_1_type = 'UNITSTATOFFENSE' THEN trim(trailing '%' from secondary_1_value)::numeric
                        WHEN secondary_2_type = 'UNITSTATOFFENSE' THEN trim(trailing '%' from secondary_2_value)::numeric
                        WHEN secondary_3_type = 'UNITSTATOFFENSE' THEN trim(trailing '%' from secondary_3_value)::numeric
                        WHEN secondary_4_type = 'UNITSTATOFFENSE' THEN trim(trailing '%' from secondary_4_value)::numeric
                    ELSE 0 END as offense,
                    CASE
                        WHEN secondary_1_type = 'UNITSTATOFFENSEPERCENTADDITIVE' THEN trim(trailing '%' from secondary_1_value)::numeric
                        WHEN secondary_2_type = 'UNITSTATOFFENSEPERCENTADDITIVE' THEN trim(trailing '%' from secondary_2_value)::numeric
                        WHEN secondary_3_type = 'UNITSTATOFFENSEPERCENTADDITIVE' THEN trim(trailing '%' from secondary_3_value)::numeric
                        WHEN secondary_4_type = 'UNITSTATOFFENSEPERCENTADDITIVE' THEN trim(trailing '%' from secondary_4_value)::numeric
                    ELSE 0 END as offense_percent
                from mods
                inner join mod_users on mods.mod_user_id = mod_users.id
                inner join members on mod_users.name = members.ally_code
                inner join guilds on guilds.id = members.guild_id
            ) mod_totals
            group by guild_id
        ");
        Schema::table('guild_unit_counts', function(Blueprint $table) {
			$table->index('guild_id');
        });
        Schema::table('guild_mod_counts', function(Blueprint $table) {
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
