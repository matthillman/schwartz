<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateZetaCharacter extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('character_zeta', function (Blueprint $table) {
            $table->unsignedInteger('character_id');
            $table->foreign('character_id')->references('id')->on('characters');
            $table->unsignedInteger('zeta_id');
            $table->foreign('zeta_id')->references('id')->on('zetas');
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
        Schema::dropIfExists('character_zeta');
    }
}
