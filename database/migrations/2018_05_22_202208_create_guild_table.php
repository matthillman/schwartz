<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGuildTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('guilds', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('guild_id')->unique();
            $table->string('name');
            $table->string('url');
            $table->unsignedInteger('gp');
            $table->boolean('schwartz')->default(false);
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
        Schema::dropIfExists('guilds');
    }
}
