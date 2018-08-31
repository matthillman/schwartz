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
		WHEN secondary_1_type = 'critical chance' THEN trim(trailing '%' from secondary_1_value)::numeric
		WHEN secondary_2_type = 'critical chance' THEN trim(trailing '%' from secondary_2_value)::numeric
		WHEN secondary_3_type = 'critical chance' THEN trim(trailing '%' from secondary_3_value)::numeric
		WHEN secondary_4_type = 'critical chance' THEN trim(trailing '%' from secondary_4_value)::numeric
	ELSE 0 END as critical_chance,
	CASE
		WHEN secondary_1_type = 'defense' THEN trim(trailing '%' from secondary_1_value)::numeric
		WHEN secondary_2_type = 'defense' THEN trim(trailing '%' from secondary_2_value)::numeric
		WHEN secondary_3_type = 'defense' THEN trim(trailing '%' from secondary_3_value)::numeric
		WHEN secondary_4_type = 'defense' THEN trim(trailing '%' from secondary_4_value)::numeric
	ELSE 0 END as defense,
	CASE
		WHEN secondary_1_type = 'defense %' THEN trim(trailing '%' from secondary_1_value)::numeric
		WHEN secondary_2_type = 'defense %' THEN trim(trailing '%' from secondary_2_value)::numeric
		WHEN secondary_3_type = 'defense %' THEN trim(trailing '%' from secondary_3_value)::numeric
		WHEN secondary_4_type = 'defense %' THEN trim(trailing '%' from secondary_4_value)::numeric
	ELSE 0 END as defense_percent,
	CASE
		WHEN secondary_1_type = 'health' THEN trim(trailing '%' from secondary_1_value)::numeric
		WHEN secondary_2_type = 'health' THEN trim(trailing '%' from secondary_2_value)::numeric
		WHEN secondary_3_type = 'health' THEN trim(trailing '%' from secondary_3_value)::numeric
		WHEN secondary_4_type = 'health' THEN trim(trailing '%' from secondary_4_value)::numeric
	ELSE 0 END as health,
	CASE
		WHEN secondary_1_type = 'health %' THEN trim(trailing '%' from secondary_1_value)::numeric
		WHEN secondary_2_type = 'health %' THEN trim(trailing '%' from secondary_2_value)::numeric
		WHEN secondary_3_type = 'health %' THEN trim(trailing '%' from secondary_3_value)::numeric
		WHEN secondary_4_type = 'health %' THEN trim(trailing '%' from secondary_4_value)::numeric
	ELSE 0 END as health_percent,
	CASE
		WHEN secondary_1_type = 'offense' THEN trim(trailing '%' from secondary_1_value)::numeric
		WHEN secondary_2_type = 'offense' THEN trim(trailing '%' from secondary_2_value)::numeric
		WHEN secondary_3_type = 'offense' THEN trim(trailing '%' from secondary_3_value)::numeric
		WHEN secondary_4_type = 'offense' THEN trim(trailing '%' from secondary_4_value)::numeric
	ELSE 0 END as offense,
	CASE
		WHEN secondary_1_type = 'offense %' THEN trim(trailing '%' from secondary_1_value)::numeric
		WHEN secondary_2_type = 'offense %' THEN trim(trailing '%' from secondary_2_value)::numeric
		WHEN secondary_3_type = 'offense %' THEN trim(trailing '%' from secondary_3_value)::numeric
		WHEN secondary_4_type = 'offense %' THEN trim(trailing '%' from secondary_4_value)::numeric
	ELSE 0 END as offense_percent,
	CASE
		WHEN secondary_1_type = 'protection' THEN trim(trailing '%' from secondary_1_value)::numeric
		WHEN secondary_2_type = 'protection' THEN trim(trailing '%' from secondary_2_value)::numeric
		WHEN secondary_3_type = 'protection' THEN trim(trailing '%' from secondary_3_value)::numeric
		WHEN secondary_4_type = 'protection' THEN trim(trailing '%' from secondary_4_value)::numeric
	ELSE 0 END as protection,
	CASE
		WHEN secondary_1_type = 'protection %' THEN trim(trailing '%' from secondary_1_value)::numeric
		WHEN secondary_2_type = 'protection %' THEN trim(trailing '%' from secondary_2_value)::numeric
		WHEN secondary_3_type = 'protection %' THEN trim(trailing '%' from secondary_3_value)::numeric
		WHEN secondary_4_type = 'protection %' THEN trim(trailing '%' from secondary_4_value)::numeric
	ELSE 0 END as protection_percent,
	CASE
		WHEN secondary_1_type = 'potency' THEN trim(trailing '%' from secondary_1_value)::numeric
		WHEN secondary_2_type = 'potency' THEN trim(trailing '%' from secondary_2_value)::numeric
		WHEN secondary_3_type = 'potency' THEN trim(trailing '%' from secondary_3_value)::numeric
		WHEN secondary_4_type = 'potency' THEN trim(trailing '%' from secondary_4_value)::numeric
	ELSE 0 END as potency,
	CASE
		WHEN secondary_1_type = 'speed' THEN trim(trailing '%' from secondary_1_value)::numeric
		WHEN secondary_2_type = 'speed' THEN trim(trailing '%' from secondary_2_value)::numeric
		WHEN secondary_3_type = 'speed' THEN trim(trailing '%' from secondary_3_value)::numeric
		WHEN secondary_4_type = 'speed' THEN trim(trailing '%' from secondary_4_value)::numeric
	ELSE 0 END as speed,
	CASE
		WHEN secondary_1_type = 'tenacity' THEN trim(trailing '%' from secondary_1_value)::numeric
		WHEN secondary_2_type = 'tenacity' THEN trim(trailing '%' from secondary_2_value)::numeric
		WHEN secondary_3_type = 'tenacity' THEN trim(trailing '%' from secondary_3_value)::numeric
		WHEN secondary_4_type = 'tenacity' THEN trim(trailing '%' from secondary_4_value)::numeric
	ELSE 0 END as tenacity
FROM mods;
        ");
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
    }
}
