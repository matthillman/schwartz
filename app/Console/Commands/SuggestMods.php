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
                    [
                        'offense' => 100,
                        'speed' => 0,
                        'crit_damage' => 80,
                        'health' => 0,
                        'defense' => 0,
                        'crit_chance' => 0,
                        'tenacity' => 0,
                        'potency' => 0,
                    ],
                    [
                        'offense' => 100,
                        'speed' => 0,
                        'crit_damage' => 80,
                        'health' => 0,
                        'defense' => 0,
                        'crit_chance' => 0,
                        'tenacity' => 0,
                        'potency' => 0,
                    ],
                    [
                        'health' => 100,
                        'defense' => 0,
                        'crit_chance' => 0,
                        'tenacity' => 0,
                        'potency' => 0,
                    ],
                ],
                'square' => ['UNITSTATOFFENSEPERCENTADDITIVE' => 100],
                'diamond' => ['UNITSTATDEFENSEPERCENTADDITIVE' => 100],
                'triangle' => [
                    'UNITSTATCRITICALDAMAGE' => 100,
                    'UNITSTATOFFENSEPERCENTADDITIVE' => 100,
                    'UNITSTATMAXHEALTHPERCENTADDITIVE' => 20,
                    'UNITSTATMAXSHIELDPERCENTADDITIVE' => 20,
                    'UNITSTATCRITICALCHANCEPERCENTADDITIVE' => 0,
                    'UNITSTATDEFENSEPERCENTADDITIVE' => 0,
                ],
                'circle' => ['UNITSTATMAXHEALTHPERCENTADDITIVE' => 100, 'UNITSTATMAXSHIELDPERCENTADDITIVE' => 80],
                'cross' => [
                    'UNITSTATOFFENSEPERCENTADDITIVE' => 100,
                    'UNITSTATMAXSHIELDPERCENTADDITIVE' => 80,
                    'UNITSTATMAXHEALTHPERCENTADDITIVE' => 80,
                    'UNITSTATACCURACY' => 20,
                    'UNITSTATRESISTANCE' => 0,
                    'UNITSTATDEFENSEPERCENTADDITIVE' => 0,
                ],
                'arrow' => [
                    'UNITSTATSPEED' => 100,
                    'UNITSTATOFFENSEPERCENTADDITIVE' => 0,
                    'UNITSTATMAXHEALTHPERCENTADDITIVE' => 0,
                    'UNITSTATMAXSHIELDPERCENTADDITIVE' => 0,
                    'UNITSTATDEFENSEPERCENTADDITIVE' => 0,
                    'UNITSTATEVASIONNEGATEPERCENTADDITIVE' => 0,
                    'UNITSTATCRITICALNEGATECHANCEPERCENTADDITIVE' => 0,
                ],
                'secondary' => [
                    'speed' => 100,
                    'critical_chance' => 20,
                    'potency' => 10,
                    'tenacity' => 20,
                    'offense' => 70,
                    'defense' => 100,
                    'health' => 30,
                    'protection' => 20,
                    'offense_percent' => 15,
                    'defense_percent' => 20,
                    'health_percent' => 20,
                    'protection_percent' => 20,
                ]
            ],
        ];

        $user = ModUser::with('stats')->where(['name' => '552325555'])->first();

        $charRanking = $ranking['DARTHTRAYA'];
        $result = [
            'square' => null,
            'diamond' => null,
            'triangle' => null,
            'circle' => null,
            'cross' => null,
            'arrow' => null,
        ];

        $charRanking['needed'] = [
            'crit_damage' => 4,
            'offense' => 4,
            'speed' => 4,

            'crit_chance' => 2,
            'tenacity' => 2,
            'defense' => 2,
            'potency' => 2,
            'health' => 2,
        ];

        $charRanking['primaryTotal'] = [];
        foreach(['square', 'diamond', 'triangle', 'circle', 'cross', 'arrow'] as $slot) {
            $charRanking['primaryTotal'][$slot] = array_reduce(array_values($charRanking[$slot]), 'max', 0);
        }
        $charRanking['secondaryTotal'] = array_reduce(array_values($charRanking['secondary']), 'max', 0);

        $done = 0;
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
                    foreach ($charRanking['sets'][$mod->set] as $set => $rank) {
                        if ($rank > 0 && $mod->set == $set) {
                            $mod->score += $charRanking['sets'][$mod->set] / 100;
                            $mod->set_score = $charRanking['sets'][$mod->set] / 100;
                        }
                    }

                    return $mod;
                })
                ->filter(function($mod) {
                    return $mod->set_score > 0;
                })
                ->map(function($mod) use ($charRanking) {
                    foreach ($charRanking[$mod->slot] as $primary => $rank) {
                        if ($rank > 0 && $mod->primary_type == $primary) {
                            $mod->score += $mod->pips * $rank / $charRanking['primaryTotal'][$mod->slot];
                            $mod->primary_score = $mod->pips * $rank / $charRanking['primaryTotal'][$mod->slot];
                        }
                    }

                    return $mod;
                })
                ->filter(function($mod) {
                    return $mod->primary_score > 0;
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
            $thisOne = $charRanking['needed'][$mod->set];
            foreach (array_keys($charRanking['needed']) as $set) {
                $remaining = $charRanking['needed'][$set];
                if ($remaining > $done || ($remaining == 4 && $thisOne == 3)) {
                    unset($charRanking['needed'][$mod->set]);
                }
            }

            $result[$mod->slot] = $mod;

            $firstPass = false;

            $done += 1;
        } while ($done < 6);

        dd(collect($result)->toJson());
    }
}
