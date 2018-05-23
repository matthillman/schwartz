<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCharactersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('characters', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('member_id');
            $table->foreign('member_id')->references('id')->on('members');
            $table->string('unit_name'); //: GRIEVOUS
            $table->foreign('unit_name')->references('base_id')->on('units');
            $table->integer('gear_level'); //: 1,
            $table->integer('power'); //: 1014,
            $table->integer('level'); //: 1,
            $table->integer('combat_type'); //: 1,
            $table->integer('rarity'); //: 4,
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
        Schema::dropIfExists('characters');
    }
}
