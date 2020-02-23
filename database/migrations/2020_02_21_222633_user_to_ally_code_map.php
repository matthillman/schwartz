<?php

use App\User;
use App\AllyCodeMap;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UserToAllyCodeMap extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ally_code_map', function (Blueprint $table) {
            $table->increments('id');
            $table->string('discord_id');
            $table->string('server_id')->nullable();
            $table->string('ally_code');
            $table->timestamps();

            $table->unique(['discord_id', 'server_id', 'ally_code']);
            $table->index('discord_id');
            $table->index(['discord_id', 'server_id']);
            $table->index('ally_code');
        });
        $existingIDs = collect(swgoh()->registration(User::whereNotNull('discord_id')->pluck('discord_id')->toArray())->first());

        $existingIDs->each(function($mapping) {
            AllyCodeMap::create(['discord_id' => $mapping['discordId'], 'ally_code' => $mapping['allyCode']]);
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ally_code_map');
    }
}
