<?php

namespace App\Console\Commands;

use DB;
use App\Unit;
use Illuminate\Console\Command;

class PullUnits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pull:units';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull all units from swogh.gg';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $response = guzzle()->get('https://swgoh.gg/api/characters/');
        $json_string = (string)$response->getBody();

        $units = collect(json_decode($json_string, true));

        DB::transaction(function() use ($units) {
            $units->each(function($unit_data) {
                $unit = Unit::firstOrNew(['base_id' => $unit_data['base_id']]);

                $unit->base_id = $unit_data['base_id'];
                $unit->name = $unit_data['name'];
                $unit->pk = $unit_data['pk'];
                $unit->url = $unit_data['url'];
                $unit->image = $unit_data['image'];
                $unit->power = $unit_data['power'];
                $unit->description = $unit_data['description'];
                $unit->combat_type = $unit_data['combat_type'];

                $unit->save();
            });
        });

        return 0;
    }
}
