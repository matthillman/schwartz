<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTerritoryWarTeamCountersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('territory_war_team_counters', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('territory_war_team_id');
            $table->foreign('territory_war_team_id')->references('id')->on('territory_war_teams');
            $table->string('name');
            $table->string('description');
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
        Schema::dropIfExists('territory_war_team_counters');
    }
}
