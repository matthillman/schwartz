<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\ModUser;
use App\Parsers\ProfileParser;
use App\Parsers\SH\Enums\ModStat;

class MigrateDataToSwgohHelp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $secondaries = DB::table('mods')->select('secondary_1_type')->distinct()
            ->whereNotNull('secondary_1_type')
            ->pluck('secondary_1_type');

        $secondaries->each(function($stat) {
            DB::update('update mods set secondary_1_type = :converted where secondary_1_type = :old', [
                'converted' => ModStat::convert($stat),
                'old' => $stat
            ]);
            DB::update('update mods set secondary_3_type = :converted where secondary_2_type = :old', [
                'converted' => ModStat::convert($stat),
                'old' => $stat
            ]);
            DB::update('update mods set secondary_3_type = :converted where secondary_3_type = :old', [
                'converted' => ModStat::convert($stat),
                'old' => $stat
            ]);
            DB::update('update mods set secondary_4_type = :converted where secondary_4_type = :old', [
                'converted' => ModStat::convert($stat),
                'old' => $stat
            ]);
        });

        $primaries = DB::table('mods')->select('primary_type')->distinct()
            ->whereNotNull('primary_type')
            ->pluck('primary_type');

        $primaries->each(function($stat) {
            DB::update('update mods set primary_type = :converted where primary_type = :old', [
                'converted' => ModStat::convert($stat, true),
                'old' => $stat
            ]);
        });

        ModUser::all()->each(function($user) {
            $parser = new ProfileParser($user->name);
            $parser->scrape();
            $user->name = $parser->getAllyCode();
            $user->save();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $secondaries = DB::table('mods')->select('secondary_1_type')->distinct()
            ->whereNotNull('secondary_1_type')
            ->pluck('secondary_1_type');

        $secondaries->each(function($stat) {
            DB::update('update mods set secondary_1_type = :converted where secondary_1_type = :old', [
                'converted' => ModStat::convertBack(ModStat::$stat()),
                'old' => $stat
            ]);
            DB::update('update mods set secondary_3_type = :converted where secondary_2_type = :old', [
                'converted' => ModStat::convertBack(ModStat::$stat()),
                'old' => $stat
            ]);
            DB::update('update mods set secondary_3_type = :converted where secondary_3_type = :old', [
                'converted' => ModStat::convertBack(ModStat::$stat()),
                'old' => $stat
            ]);
            DB::update('update mods set secondary_4_type = :converted where secondary_4_type = :old', [
                'converted' => ModStat::convertBack(ModStat::$stat()),
                'old' => $stat
            ]);
        });

        $primaries = DB::table('mods')->select('primary_type')->distinct()
            ->whereNotNull('primary_type')
            ->pluck('primary_type');

        $primaries->each(function($stat) {
            DB::update('update mods set primary_type = :converted where primary_type = :old', [
                'converted' => ModStat::convertBack(ModStat::$stat(), true),
                'old' => $stat
            ]);
        });
    }
}
