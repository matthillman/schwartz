<?php

namespace App\Console\Commands;

use DB;
use App\Unit;
use App\Zeta;
use Illuminate\Console\Command;

class PullUnits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swgoh:units';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull the unit list from swogh.help';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $units = swgoh()->getUnitData();

        DB::transaction(function() use ($units) {
            $units->each(function($unit_data) {
                $unit = Unit::firstOrNew(['base_id' => $unit_data['baseId']]);

                $unit->name = $unit_data['nameKey'];
                $unit->description = $unit_data['descKey'];
                $unit->image = $unit_data['thumbnailName'];
                $unit->combat_type = $unit_data['combatType'];
                $unit->pk = $unit_data['baseId'];

                $unit->url = $unit->url ?? '';
                $unit->power = $unit->power ?? $unit_data['basePower'];

                $unit->save();
            });
        });

        $zetas = swgoh()->getZetaData();

        DB::transaction(function() use ($units, $zetas) {
            $zetas->each(function($data) use ($units) {
                $character = $units->first(function($unit) use ($data) {
                    return $unit['skillReferenceList']->contains($data['id']);
                });

                if (is_null($character)) {
                    \Log::error("Failed to find character for zeta", [$data]);
                    return;
                }

                $zeta = Zeta::firstOrNew([
                    'name' => $data['name'],
                    'character_id' => $character['baseId']
                ]);
                $zeta->class = $data['class'];
                $zeta->skill_id = $data['id'];
                $zeta->save();
            });
        });

        return 0;
    }
}
