<?php

use App\Member;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MigrateRawDataToSeparateTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('members_raw', function (Blueprint $table) {
            $table->unsignedBigInteger('member_id')->unique();
            $table->json('data')->default('[]');
            $table->foreign('member_id')->references('id')->on('members')->onDelete('cascade');
        });


        DB::insert('insert into members_raw (member_id, data) select id, raw from members;');

        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn('raw');
        });

        Schema::create('characters_raw', function (Blueprint $table) {
            $table->unsignedBigInteger('character_id')->unique();
            $table->json('data')->default('[]');
            $table->foreign('character_id')->references('id')->on('characters')->onDelete('cascade');
        });

        DB::insert('insert into characters_raw (character_id, data) select id, raw from characters;');
        DB::update('update characters set raw = ?', ['[]']);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('members', function (Blueprint $table) {
            $table->json('raw')->default('[]');
        });

        DB::insert('update members m set raw = d.data from members_raw d where m.id = d.member_id;');

        DB::insert('update characters c set raw = d.data from characters_raw d where c.id = d.character_id;');

        Schema::dropIfExists('members_raw');
        Schema::dropIfExists('characters_raw');
    }
}
