<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSquadGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('squad_groups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->bigInteger('guild_id')->default(0);
            $table->boolean('publish')->default(false);
            $table->timestamps();
        });

        $globalGroup = new App\SquadGroup;
        $globalGroup->name = 'Global';
        $globalGroup->save();

        Schema::table('squads', function (Blueprint $table) {
            $table->bigInteger('squad_group_id')->default(1);
            $table->foreign('squad_group_id')->references('id')->on('squad_groups')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('squad_groups');
    }
}
