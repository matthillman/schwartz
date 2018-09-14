<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUnitModPreferencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('unit_mod_preferences', function (Blueprint $table) {
            $table->increments('id');
            $table->string('unit_id');
            $table->foreign('unit_id')->references('base_id')->on('units');

            $table->smallInteger('set_health');
            $table->smallInteger('set_defense');
            $table->smallInteger('set_crit_damage');
            $table->smallInteger('set_crit_chance');
            $table->smallInteger('set_tenacity');
            $table->smallInteger('set_offense');
            $table->smallInteger('set_potency');
            $table->smallInteger('set_speed');

            $table->smallInteger('square_offense');
            $table->smallInteger('diamond_defense');

            $table->smallInteger('triangle_crit_damage');
            $table->smallInteger('triangle_crit_chance');
            $table->smallInteger('triangle_offense');
            $table->smallInteger('triangle_health');
            $table->smallInteger('triangle_protection');
            $table->smallInteger('triangle_defense');

            $table->smallInteger('circle_health');
            $table->smallInteger('circle_protection');

            $table->smallInteger('cross_offense');
            $table->smallInteger('cross_protection');
            $table->smallInteger('cross_health');
            $table->smallInteger('cross_potency');
            $table->smallInteger('cross_tenacity');
            $table->smallInteger('cross_defense');

            $table->smallInteger('arrow_speed');
            $table->smallInteger('arrow_offense');
            $table->smallInteger('arrow_health');
            $table->smallInteger('arrow_protection');
            $table->smallInteger('arrow_defense');
            $table->smallInteger('arrow_accuracy');
            $table->smallInteger('arrow_crit_avoid');

            $table->smallInteger('secondary_');
            $table->smallInteger('secondary_speed');
            $table->smallInteger('secondary_crit_chance');
            $table->smallInteger('secondary_potency');
            $table->smallInteger('secondary_tenacity');
            $table->smallInteger('secondary_offense');
            $table->smallInteger('secondary_defense');
            $table->smallInteger('secondary_health');
            $table->smallInteger('secondary_protection');
            $table->smallInteger('secondary_offense_percent');
            $table->smallInteger('secondary_defense_percent');
            $table->smallInteger('secondary_health_percent');
            $table->smallInteger('secondary_protection_percent');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('unit_mod_preferences');
    }
}
