<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Storage;
use App\Unit;
use App\Member;
use App\Character;

class CalculateRemainingAbilities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swgoh:abilities {ally} {unit}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Shows the remaining ability cost for a unit';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $member = Member::where('ally_code', $this->argument('ally'))->firstOrFail();
        $unit = Unit::search($this->argument('unit'))->whereExists('category_list')->first();
        $character = $member->characters()->where('unit_name', $unit->base_id)->firstOrFail();

        $this->info("Processing {$character->unit_name} for {$member->player}");


        $skills = collect(json_decode(Storage::disk('game_data')->get('skillList.json'), true));
        $recipes = collect(json_decode(Storage::disk('game_data')->get('recipeList.json'), true));

        $totals = [];
        foreach ($character->rawData->data['skillList'] as $skill) {
            $this->line("Skill data " . $skill['id']);
            $skillDef = $skills->where('id', $skill['id'])->first();
            $tiers = collect($skillDef['tierList']);

            if ($skill['tier'] < $tiers->count()) {
                foreach ($tiers->slice($skill['tier'] + 1) as $tier) {
                    $recipe = $recipes->where('id', $tier['recipeId'])->first();

                    foreach ($recipe['ingredientsList'] as $ingredient) {
                        $this->info('   👉🏻 ' . $ingredient['id'] . ': ' . $ingredient['maxQuantity']);
                        $total[$ingredient['id']] = ($total[$ingredient['id']] ?? 0) + $ingredient['maxQuantity'];
                    }
                }
            }
        }

        dd($total);
    }
}
