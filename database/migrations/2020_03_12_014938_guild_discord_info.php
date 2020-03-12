<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class GuildDiscordInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('guilds', function(Blueprint $table) {
            $table->string('admin_channel')->nullable();
            $table->string('officer_role_regex')->nullable();
            $table->string('member_role_regex')->nullable();
        });
        Schema::table('discord_roles', function (Blueprint $table) {
            $table->string('username')->nullable();
            $table->string('discriminator')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
