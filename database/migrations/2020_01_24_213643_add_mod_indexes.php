<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddModIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('mods', function(Blueprint $table) {
			$table->index('mod_user_id');
        });
        Schema::table('mod_users', function(Blueprint $table) {
			$table->index('name');
        });
        Schema::table('characters', function(Blueprint $table) {
			$table->index('member_id');
        });
        Schema::table('members', function(Blueprint $table) {
			$table->index('ally_code');
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
