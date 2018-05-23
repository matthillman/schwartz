<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUnitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('units', function (Blueprint $table) {
            $table->increments('id');
            $table->string('base_id')->unique(); //: "AAYLASECURA",
            $table->string('name'); //: "Aayla Secura",
            $table->string('pk'); //: 80,
            $table->string('url'); //: "https://swgoh.gg/characters/aayla-secura/",
            $table->string('image'); //: "//swgoh.gg/static/img/assets/tex.charui_aaylasecura.png",
            $table->string('power'); //: 17523,
            $table->string('description'); //: "Versatile attacker with high survivability through Dodge, Hitpoints, and self healing.",
            $table->string('combat_type'); //: 1
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
        Schema::dropIfExists('units');
    }
}
