<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateModsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mods', function (Blueprint $table) {
            $table->increments('id');

            $table->string('uid')->unique();
            $table->unsignedInteger('mod_user_id');
            $table->foreign('mod_user_id')->references('id')->on('mod_users');

            $table->string('name');
            $table->string('location');
            $table->string('slot');
            $table->string('set');
            $table->integer('pips');
            $table->string('level');

            $table->string('primary_type');
            $table->string('primary_value');

            $table->string('secondary_1_type')->nullable();
            $table->string('secondary_1_value')->nullable();
            $table->string('secondary_2_type')->nullable();
            $table->string('secondary_2_value')->nullable();
            $table->string('secondary_3_type')->nullable();
            $table->string('secondary_3_value')->nullable();
            $table->string('secondary_4_type')->nullable();
            $table->string('secondary_4_value')->nullable();

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
        Schema::dropIfExists('mods');
    }
}
