<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMigrationColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('members', function (Blueprint $table) {
            $table->string('player_id')->nullable();
            $table->json('raw')->default('[]');
        });

        Schema::table('units', function (Blueprint $table) {
            $table->json('crew_list')->default('[]');
        });

        Schema::create('stat_mod_list', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('slot');
            $table->integer('set');
            $table->integer('rarity');
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
        Schema::table('members', function (Blueprint $table) {
            //
        });
    }
}
