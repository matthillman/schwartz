<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCharacterModsView extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("CREATE view character_mods AS
            select mods.*, characters.id as character_id from mods
            join mod_users on mods.mod_user_id = mod_users.id
            join members on members.ally_code = mod_users.name
            join characters on characters.member_id = members.id
            where mods.location = characters.unit_name
        ;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DROP view character_mods;");
    }
}
