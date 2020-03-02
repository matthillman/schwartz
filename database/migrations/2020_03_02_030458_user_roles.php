<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UserRoles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('discord_roles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('discord_id');
            $table->unique('discord_id');
            $table->json('roles')->default('[]');
            $table->timestamps();
        });
        Schema::table('guilds', function (Blueprint $table) {
            $table->string('server_id')->nullable();
        });
        Schema::table('members', function (Blueprint $table) {
            $table->tinyInteger('member_level')->default(2);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('discord_roles');
    }
}
