<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeGuildStats extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('guild_stats', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('guild_id')->unique();
            $table->foreign('guild_id')->references('id')->on('guilds')->onDelete('cascade');
            $table->jsonb('unit_data')->default('[]');
            $table->jsonb('mod_data')->default('[]');

            $table->timestamps();
            $table->index('guild_id');
        });
        Schema::create('member_statistics', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('member_id')->unique();
            $table->foreign('member_id')->references('id')->on('members')->onDelete('cascade');
            $table->jsonb('data')->default('[]');

            $table->timestamps();
            $table->index('member_id');
        });

        DB::statement("INSERT into guild_stats(guild_id, unit_data, mod_data, created_at, updated_at)
           SELECT
                guilds.id as guild_id,
                row_to_json(guild_unit_counts)::jsonb - 'guild_id' as unit_data,
                row_to_json(guild_mod_counts)::jsonb - 'guild_id' as mod_data,
                now() as created_at,
                now() as updated_at
            FROM guild_unit_counts
            join guild_mod_counts on guild_unit_counts.guild_id = guild_mod_counts.guild_id
            join guilds on guilds.guild_id = guild_unit_counts.guild_id;
        ");

        DB::statement("INSERT into member_statistics(member_id, data, created_at, updated_at)
            select
                members.id,
                row_to_json(member_stats)::jsonb - 'ally_code' as data,
                now() as created_at,
                now() as updated_at
            from member_stats
            join members on members.ally_code = member_stats.ally_code;
        ");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('guild_stats');
        Schema::dropIfExists('member_statistics');
    }
}
