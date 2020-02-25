<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSquadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('squads', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('location_id')->default('');
            $table->string('leader_id');
            $table->string('display');
            $table->string('description');
            $table->json('additional_members');
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('edit_teams')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('squads');
    }
}
