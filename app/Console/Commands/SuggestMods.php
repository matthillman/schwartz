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
            'PADMEAMIDALA' => [
                'sets' => [
                    [
                        'offense' => 0,
                        'speed' => 100,
                        'crit_damage' => 0,
                        'health' => 0,
                        'defense' => 0,
                        'crit_chance' => 0,
                        'tenacity' => 0,
                        'potency' => 0,
                    ],
                    [
                        'offense' => 0,
                        'speed' => 100,
                        'crit_damage' => 0,
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
                    'UNITSTATCRITICALDAMAGE' => 0,
                    'UNITSTATOFFENSEPERCENTADDITIVE' => 0,
                    'UNITSTATMAXHEALTHPERCENTADDITIVE' => 100,
                    'UNITSTATMAXSHIELDPERCENTADDITIVE' => 0,
                    'UNITSTATCRITICALCHANCEPERCENTADDITIVE' => 0,
                    'UNITSTATDEFENSEPERCENTADDITIVE' => 0,
                ],
                'circle' => ['UNITSTATMAXHEALTHPERCENTADDITIVE' => 100, 'UNITSTATMAXSHIELDPERCENTADDITIVE' => 10],
                'cross' => [
                    'UNITSTATOFFENSEPERCENTADDITIVE' => 0,
                    'UNITSTATMAXSHIELDPERCENTADDITIVE' => 10,
                    'UNITSTATMAXHEALTHPERCENTADDITIVE' => 100,
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
                    'critical_chance' => 0,
                    'potency' => 0,
                    'tenacity' => 0,
                    'offense' => 0,
                    'defense' => 0,
                    'health' => 90,
                    'protection' => 0,
                    'offense_percent' => 0,
                    'defense_percent' => 0,
                    'health_percent' => 50,
                    'protection_percent' => 0,
                ]
            ],
        ];

        $target = [
            'PADMEAMIDALA' => [
                'UNITSTATSPEED' => 286,
            ],
        ];

        $user = ModUser::with('stats')->where(['name' => '552325555'])->first();
        $member = Member::where('ally_code', '552325555')->first();
        $memberUnit = $member->characters()->where('unit_name', 'PADMEAMIDALA');

        $charRanking = $ranking['PADMEAMIDALA'];
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
        $modQuery = $user->stats()->select('mod_stats.*');
        foreach ($charRanking['secondary'] as $secondary => $rank) {
            if ($rank > 0) {
                $modQuery->leftJoin("mod_stat_${secondary}", "mod_stat_${secondary}.id", '=', 'mod_stats.id');
                $modQuery->selectRaw("mod_stat_${secondary}.percentile * ". $rank / $charRanking['secondaryTotal'] ." as ${secondary}_score");
            }
        }
        $allMods = $modQuery->get();
        do {
            $this->line('Char ranking needed: ' . json_encode($charRanking['needed']));

            $availableSets = array_keys($charRanking['needed']);
            $availableSlots = collect($result)->filter(function ($r) { return is_null($r); })->keys()->all();
            $modResults = $allMods
                ->filter(function($mod) use ($availableSets, $availableSlots, $firstPass) {
                    return in_array($mod->set, $availableSets) && in_array($mod->slot, $availableSlots) && (!$firstPass || $mod->slot != 'arrow');
                })
                ->map(function($mod) use ($charRanking) {
                    $mod->score = 0;
                    foreach ($charRanking['sets'] as $group) {
                        if (isset($group[$mod->set])) {
                            $rank = $group[$mod->set];
                            if ($rank > 0) {
                                $mod->score += $rank / 100;
                                $mod->set_score = $rank / 100;
                                break;
                            }
                        }

                    }

                    return $mod;
                })
                ->filter(function($mod) {
                    return $mod->set_score > 0;
                });

            $this->line('Set filter resulted in ' . $modResults->count() . ' mods');

            $modResults = $modResults
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
                });
            $this->line('Primary stat filter resulted in ' . $modResults->count() . ' mods');

            $modResults = $modResults
                ->map(function($mod) use ($charRanking) {
                    foreach ($charRanking['secondary'] as $secondary => $rank) {
                        if ($rank > 0 && isset($mod->{"${secondary}_score"})) {
                            $base = $mod->{"${secondary}_score"};

                            $ex = exp(($base / 100 * 4) - 4) * 100;

                            $mod->score += $ex;
                            $mod->{"secondary_score_${secondary}"} = $ex;
                        }
                    }

                    return $mod;
                })
                ->sortByDesc('score');

            $this->line('Secondary stat filter resulted in ' . $modResults->count() . ' mods');

            $mod = $modResults->first();

            if (is_null($mod)) {
                dd($modResults);
            }

            $this->line("Picked a mod: ". $mod->toJson());

            $charRanking['needed'][$mod->set] -= 1;

            if ($charRanking['needed'][$mod->set] == 0) {
                unset($charRanking['needed'][$mod->set]);
            }

            $result[$mod->slot] = $mod;

            $firstPass = false;

            $done += 1;
        } while ($done < 6);

        dd(collect($result)->toJson());
    }
}
