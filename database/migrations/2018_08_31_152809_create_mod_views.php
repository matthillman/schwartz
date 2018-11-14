<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateModViews extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("CREATE MATERIALIZED VIEW mod_secondaries AS
            select id, secondary_1_type as type, trim(trailing '%' from secondary_1_value)::numeric as value from mods where secondary_1_type is not null
            union
            select id, secondary_2_type as type, trim(trailing '%' from secondary_2_value)::numeric as value from mods where secondary_2_type is not null
            union
            select id, secondary_3_type as type, trim(trailing '%' from secondary_3_value)::numeric as value from mods where secondary_3_type is not null
            union
            select id, secondary_4_type as type, trim(trailing '%' from secondary_4_value)::numeric as value from mods where secondary_4_type is not null
        ;");

        Schema::table('mod_secondaries', function(Blueprint $table) {
			$table->index(['id', 'type']);
        });

        DB::statement("CREATE VIEW mod_stats AS
SELECT id, uid, mod_user_id, name, location, slot, set, pips, level, primary_type, trim(trailing '%' from primary_value)::numeric as primary_value, tier,
	CASE
		WHEN secondary_1_type = 'UNITSTATCRITICALCHANCEPERCENTADDITIVE' THEN trim(trailing '%' from secondary_1_value)::numeric
		WHEN secondary_2_type = 'UNITSTATCRITICALCHANCEPERCENTADDITIVE' THEN trim(trailing '%' from secondary_2_value)::numeric
		WHEN secondary_3_type = 'UNITSTATCRITICALCHANCEPERCENTADDITIVE' THEN trim(trailing '%' from secondary_3_value)::numeric
		WHEN secondary_4_type = 'UNITSTATCRITICALCHANCEPERCENTADDITIVE' THEN trim(trailing '%' from secondary_4_value)::numeric
	ELSE 0 END as critical_chance,
	CASE
		WHEN secondary_1_type = 'UNITSTATDEFENSE' THEN trim(trailing '%' from secondary_1_value)::numeric
		WHEN secondary_2_type = 'UNITSTATDEFENSE' THEN trim(trailing '%' from secondary_2_value)::numeric
		WHEN secondary_3_type = 'UNITSTATDEFENSE' THEN trim(trailing '%' from secondary_3_value)::numeric
		WHEN secondary_4_type = 'UNITSTATDEFENSE' THEN trim(trailing '%' from secondary_4_value)::numeric
	ELSE 0 END as defense,
	CASE
		WHEN secondary_1_type = 'UNITSTATDEFENSEPERCENTADDITIVE' THEN trim(trailing '%' from secondary_1_value)::numeric
		WHEN secondary_2_type = 'UNITSTATDEFENSEPERCENTADDITIVE' THEN trim(trailing '%' from secondary_2_value)::numeric
		WHEN secondary_3_type = 'UNITSTATDEFENSEPERCENTADDITIVE' THEN trim(trailing '%' from secondary_3_value)::numeric
		WHEN secondary_4_type = 'UNITSTATDEFENSEPERCENTADDITIVE' THEN trim(trailing '%' from secondary_4_value)::numeric
	ELSE 0 END as defense_percent,
	CASE
		WHEN secondary_1_type = 'UNITSTATMAXHEALTH' THEN trim(trailing '%' from secondary_1_value)::numeric
		WHEN secondary_2_type = 'UNITSTATMAXHEALTH' THEN trim(trailing '%' from secondary_2_value)::numeric
		WHEN secondary_3_type = 'UNITSTATMAXHEALTH' THEN trim(trailing '%' from secondary_3_value)::numeric
		WHEN secondary_4_type = 'UNITSTATMAXHEALTH' THEN trim(trailing '%' from secondary_4_value)::numeric
	ELSE 0 END as health,
	CASE
		WHEN secondary_1_type = 'UNITSTATMAXHEALTHPERCENTADDITIVE' THEN trim(trailing '%' from secondary_1_value)::numeric
		WHEN secondary_2_type = 'UNITSTATMAXHEALTHPERCENTADDITIVE' THEN trim(trailing '%' from secondary_2_value)::numeric
		WHEN secondary_3_type = 'UNITSTATMAXHEALTHPERCENTADDITIVE' THEN trim(trailing '%' from secondary_3_value)::numeric
		WHEN secondary_4_type = 'UNITSTATMAXHEALTHPERCENTADDITIVE' THEN trim(trailing '%' from secondary_4_value)::numeric
	ELSE 0 END as health_percent,
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
	ELSE 0 END as offense_percent,
	CASE
		WHEN secondary_1_type = 'UNITSTATMAXSHIELD' THEN trim(trailing '%' from secondary_1_value)::numeric
		WHEN secondary_2_type = 'UNITSTATMAXSHIELD' THEN trim(trailing '%' from secondary_2_value)::numeric
		WHEN secondary_3_type = 'UNITSTATMAXSHIELD' THEN trim(trailing '%' from secondary_3_value)::numeric
		WHEN secondary_4_type = 'UNITSTATMAXSHIELD' THEN trim(trailing '%' from secondary_4_value)::numeric
	ELSE 0 END as protection,
	CASE
		WHEN secondary_1_type = 'UNITSTATMAXSHIELDPERCENTADDITIVE' THEN trim(trailing '%' from secondary_1_value)::numeric
		WHEN secondary_2_type = 'UNITSTATMAXSHIELDPERCENTADDITIVE' THEN trim(trailing '%' from secondary_2_value)::numeric
		WHEN secondary_3_type = 'UNITSTATMAXSHIELDPERCENTADDITIVE' THEN trim(trailing '%' from secondary_3_value)::numeric
		WHEN secondary_4_type = 'UNITSTATMAXSHIELDPERCENTADDITIVE' THEN trim(trailing '%' from secondary_4_value)::numeric
	ELSE 0 END as protection_percent,
	CASE
		WHEN secondary_1_type = 'UNITSTATACCURACY' THEN trim(trailing '%' from secondary_1_value)::numeric
		WHEN secondary_2_type = 'UNITSTATACCURACY' THEN trim(trailing '%' from secondary_2_value)::numeric
		WHEN secondary_3_type = 'UNITSTATACCURACY' THEN trim(trailing '%' from secondary_3_value)::numeric
		WHEN secondary_4_type = 'UNITSTATACCURACY' THEN trim(trailing '%' from secondary_4_value)::numeric
	ELSE 0 END as potency,
	CASE
		WHEN secondary_1_type = 'UNITSTATSPEED' THEN trim(trailing '%' from secondary_1_value)::numeric
		WHEN secondary_2_type = 'UNITSTATSPEED' THEN trim(trailing '%' from secondary_2_value)::numeric
		WHEN secondary_3_type = 'UNITSTATSPEED' THEN trim(trailing '%' from secondary_3_value)::numeric
		WHEN secondary_4_type = 'UNITSTATSPEED' THEN trim(trailing '%' from secondary_4_value)::numeric
	ELSE 0 END as speed,
	CASE
		WHEN secondary_1_type = 'UNITSTATRESISTANCE' THEN trim(trailing '%' from secondary_1_value)::numeric
		WHEN secondary_2_type = 'UNITSTATRESISTANCE' THEN trim(trailing '%' from secondary_2_value)::numeric
		WHEN secondary_3_type = 'UNITSTATRESISTANCE' THEN trim(trailing '%' from secondary_3_value)::numeric
		WHEN secondary_4_type = 'UNITSTATRESISTANCE' THEN trim(trailing '%' from secondary_4_value)::numeric
	ELSE 0 END as tenacity
FROM mods;
		");

		DB::statement("create view mod_stat_critical_chance as select id, critical_chance, ntile(100) over (order by critical_chance) as percentile from mod_stats where critical_chance > 0;");
		DB::statement("create view mod_stat_defense as select id, defense, ntile(100) over (order by defense) as percentile from mod_stats where defense > 0;");
		DB::statement("create view mod_stat_defense_percent as select id, defense_percent, ntile(100) over (order by defense_percent) as percentile from mod_stats where defense_percent > 0;");
		DB::statement("create view mod_stat_health as select id, health, ntile(100) over (order by health) as percentile from mod_stats where health > 0;");
		DB::statement("create view mod_stat_health_percent as select id, health_percent, ntile(100) over (order by health_percent) as percentile from mod_stats where health_percent > 0;");
		DB::statement("create view mod_stat_offense as select id, offense, ntile(100) over (order by offense) as percentile from mod_stats where offense > 0;");
		DB::statement("create view mod_stat_offense_percent as select id, offense_percent, ntile(100) over (order by offense_percent) as percentile from mod_stats where offense_percent > 0;");
		DB::statement("create view mod_stat_protection as select id, protection, ntile(100) over (order by protection) as percentile from mod_stats where protection > 0;");
		DB::statement("create view mod_stat_protection_percent as select id, protection_percent, ntile(100) over (order by protection_percent) as percentile from mod_stats where protection_percent > 0;");
		DB::statement("create view mod_stat_potency as select id, potency, ntile(100) over (order by potency) as percentile from mod_stats where potency > 0;");
		DB::statement("create view mod_stat_speed as select id, speed, ntile(100) over (order by speed) as percentile from mod_stats where speed > 0;");
		DB::statement("create view mod_stat_tenacity as select id, tenacity, ntile(100) over (order by tenacity) as percentile from mod_stats where tenacity > 0;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mod_secondaries', function(Blueprint $table) {
            $table->dropIndex(['id', 'type']);
        });
        DB::statement("DROP MATERIALIZED VIEW mod_secondaries");
		DB::statement("DROP VIEW mod_stats");
		DB::statement("DROP VIEW mod_stat_critical_chance");
		DB::statement("DROP VIEW mod_stat_defense");
		DB::statement("DROP VIEW mod_stat_defense_percent");
		DB::statement("DROP VIEW mod_stat_health");
		DB::statement("DROP VIEW mod_stat_health_percent");
		DB::statement("DROP VIEW mod_stat_offense");
		DB::statement("DROP VIEW mod_stat_offense_percent");
		DB::statement("DROP VIEW mod_stat_protection");
		DB::statement("DROP VIEW mod_stat_protection_percent");
		DB::statement("DROP VIEW mod_stat_potency");
		DB::statement("DROP VIEW mod_stat_speed");
		DB::statement("DROP VIEW mod_stat_tenacity");
    }
}
