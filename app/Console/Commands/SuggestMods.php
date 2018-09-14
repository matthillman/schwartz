<?php

namespace App\Console\Commands;

use DB;
use App\ModUser;
use Illuminate\Console\Command;

class SuggestMods extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mods:suggest';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Suggest some mods';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // 'square', 'diamond', 'triangle', 'circle', 'cross', 'arrow'
        // 'health', 'defense', 'critdamage', 'critchance', 'tenacity', 'offense', 'potency', 'speed'
        // speed, critchance, critdamage, potency, tenacity, accuracy, critavoidance, offense, defense, health, protection
        // speed, critchance, potency, tenacity, offense, defense, health, protection, offense %, defense %, health %, protection %
        $ranking = [
            'DARTHTRAYA' => [
                'sets' => [
                    'offense',
                    'offense',
                    'health',
                ],
                'square' => ['offense' => 5],
                'diamond' => ['defense' => 5],
                'triangle' => [
                    'critical damage' => 5,
                    'offense' => 5,
                    'health' => 1,
                    'protection' => 1,
                    'critical chance' => 0,
                    'defense' => 0,
                ],
                'circle' => ['health' => 5, 'protection' => 4],
                'cross' => [
                    'offense' => 5,
                    'protection' => 4,
                    'health' => 4,
                    'potency' => 1,
                    'tenacity' => 0,
                    'defense' => 0,
                ],
                'arrow' => [
                    'speed' => 5,
                    'offense' => 0,
                    'health' => 0,
                    'protection' => 0,
                    'defense' => 0,
                    'accuracy' => 0,
                    'critical avoidance' => 0,
                ],
                'secondary' => [
                    'speed' => 100,
                    'critical_chance' => 20,
                    'potency' => 10,
                    'tenacity' => 1,
                    'offense' => 70,
                    'defense' => 5,
                    'health' => 30,
                    'protection' => 1,
                    'offense_percent' => 15,
                    'defense_percent' => 1,
                    'health_percent' => 1,
                    'protection_percent' => 1,
                ]
            ],
        ];

        $user = ModUser::with('stats')->where(['name' => 'fraxgoran'])->first();

        $charRanking = $ranking['DARTHTRAYA'];
        $result = [
            'square' => null,
            'diamond' => null,
            'triangle' => null,
            'circle' => null,
            'cross' => null,
            'arrow' => null,
        ];
        $charRanking['secondaryTotal'] = array_reduce(array_values($charRanking['secondary']), 'max', 0);
        $charRanking['needed'] = [
            'offense' => 4,
            'health' => 2,
        ];
        $charRanking['primaryTotal'] = [];
        foreach(['square', 'diamond', 'triangle', 'circle', 'cross', 'arrow'] as $slot => $ranks) {
            $charRanking['primaryTotal'][$slot] = array_reduce(array_values($ranks), 'max', 0);
        }
        $done = false;
        $firstPass = true;
        do {
            $mod = $user->stats
                ->filter(function($mod) use ($charRanking, $result) {
                    return in_array($mod->set, array_keys($charRanking['needed'])) && is_null($result[$mod->slot]);
                })
                ->filter(function($mod) use ($firstPass) {
                    return !$firstPass || $mod->slot != 'arrow';
                })
                ->map(function($mod) use ($charRanking) {
                    $mod->score = 0;
                    foreach ($charRanking[$mod->slot] as $primary => $rank) {
                        if ($rank > 0 && $mod->primary_type == $primary) {
                            $mod->score += $mod->pips * $rank / $charRanking['primaryTotal'][$mod->slot] / 5 * 100;
                            $mod->{"primary_score"} = $mod->pips * $rank / $charRanking['primaryTotal'][$mod->slot] / 5 * 100;
                        }
                    }

                    return $mod;
                })
                ->map(function($mod) use ($charRanking) {
                    foreach ($charRanking['secondary'] as $secondary => $rank) {
                        if ($rank > 0 && $mod->{$secondary} > 0) {
                            $ranked = DB::table("mod_stat_${secondary}")->where('id', $mod->id)->value('percentile');
                            $base = $ranked * $rank / $charRanking['secondaryTotal'];

                            $ex = exp(($base / 100 * 4) - 4) * 100;

                            $mod->score += $ex;
                            $mod->{"secondary_score_${secondary}"} = $ex;
                        }
                    }

                    return $mod;
                })
                ->sortByDesc('score')
                ->first();

            $this->line("Picked a mod: ". $mod->toJson());

            $charRanking['needed'][$mod->set] -= 1;
            if ($charRanking['needed'][$mod->set] == 0) {
                unset($charRanking['needed'][$mod->set]);
            }

            $result[$mod->slot] = $mod;

            $firstPass = false;

            $done = array_reduce(array_values($result), function ($done, $val) {
                return $done && !is_null($val);
            }, true);
        } while (!$done);

        dd(collect($result)->toJson());
    }
}
