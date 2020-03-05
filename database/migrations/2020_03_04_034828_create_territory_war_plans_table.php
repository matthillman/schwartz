<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTerritoryWarPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('territory_war_plans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('guild_id');
            $table->bigInteger('squad_group_id');
            $table->string('name');
            $table->string('notes')->default('');
            $table->json('zone_1')->default('[]');
            $table->string('zone_1_notes')->default('');
            $table->json('zone_2')->default('[]');
            $table->string('zone_2_notes')->default('');
            $table->json('zone_3')->default('[]');
            $table->string('zone_3_notes')->default('');
            $table->json('zone_4')->default('[]');
            $table->string('zone_4_notes')->default('');
            $table->json('zone_5')->default('[]');
            $table->string('zone_5_notes')->default('');
            $table->json('zone_6')->default('[]');
            $table->string('zone_6_notes')->default('');
            $table->json('zone_7')->default('[]');
            $table->string('zone_7_notes')->default('');
            $table->json('zone_8')->default('[]');
            $table->string('zone_8_notes')->default('');
            $table->json('zone_9')->default('[]');
            $table->string('zone_9_notes')->default('');
            $table->json('zone_10')->default('[]');
            $table->string('zone_10_notes')->default('');
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
        Schema::dropIfExists('territory_war_plans');
    }
}
