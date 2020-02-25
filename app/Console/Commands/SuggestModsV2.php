<?php

namespace App\Console\Commands;

use DB;
use App\Member;
use App\ModUser;
use Illuminate\Console\Command;


class SuggestModsV2 extends Command
{
    use \App\Util\ScoresMods;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mods:suggest2';

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
                        'speed' => 95,
                        'critdamage' => 0,
                        'health' => 100,
                        'defense' => 0,
                        'critchance' => 0,
                        'tenacity' => 0,
                        'potency' => 50,
                    ],
                    [
                        'offense' => 0,
                        'speed' => 95,
                        'critdamage' => 0,
                        'health' => 100,
                        'defense' => 0,
                        'critchance' => 0,
                        'tenacity' => 0,
                        'potency' => 0,
                    ],
                    [
                        'health' => 100,
                        'defense' => 0,
                        'critchance' => 0,
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
                    'UNITSTATMAXHEALTHPERCENTADDITIVE' => 80,
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
                ],
                'target' => [
                    'UNITSTATSPEED' => 290,
                ]
            ],
        ];

        $user = ModUser::with('stats')->where(['name' => '552325555'])->first();
        $member = Member::where('ally_code', '552325555')->first();
        $memberUnit = $member->characters()->where('unit_name', 'PADMEAMIDALA')->first();

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
            'critdamage' => 4,
            'offense' => 4,
            'speed' => 4,

            'critchance' => 2,
            'tenacity' => 2,
            'defense' => 2,
            'potency' => 2,
            'health' => 2,
        ];

        $secondaryList = [ 'speed', 'critical_chance', 'potency', 'tenacity', 'offense', 'defense', 'health', 'protection', 'offense_percent', 'defense_percent', 'health_percent', 'protection_percent' ];
        $setCounts = [
            'offense' => 4,
            'speed' => 4,
            'critdamage' => 4,
            'health' => 2,
            'defense' => 2,
            'critchance' => 2,
            'tenacity' => 2,
            'potency' => 2,
        ];

        $charRanking['primaryTotal'] = [];
        foreach(['square', 'diamond', 'triangle', 'circle', 'cross', 'arrow'] as $slot) {
            $charRanking['primaryTotal'][$slot] = array_reduce(array_values($charRanking[$slot]), 'max', 0);
        }
        $charRanking['secondaryTotal'] = array_reduce(array_values($charRanking['secondary']), 'max', 0);

        $done = 0;
        $firstPass = true;

        $modQuery = $user->stats()->select('mod_stats.*');
        foreach ($secondaryList as $secondary) {
            $modQuery->leftJoin("mod_stat_${secondary}", "mod_stat_${secondary}.id", '=', 'mod_stats.id');
            $modQuery->selectRaw("mod_stat_${secondary}.percentile as ${secondary}_percentile");
        }

        $allMods = $modQuery->get();

        // Do some pre-sorting

        $modLookup = $allMods->groupBy('set')->toBase()->mapWithKeys(function($mods, $set) { return [$set => collect($mods)->groupBy('slot')]; });

        // Calculate what we need

        $baseSpeed = $memberUnit->base_speed;
        $canDo6dotMods = $memberUnit->gear_level >= 12;
        $speedSetBonus = intval($baseSpeed / 10); // approx… anything less than g13 and this *could* be off
        $maxSpeedFromArrow = $canDo6dotMods ? 32 : 30;

        $speedNeeded = $target[$memberUnit->unit_name]['UNITSTATSPEED'] - $baseSpeed;
        $speedNeededWithSpeedArrow = $speedNeeded - $maxSpeedFromArrow;
        $speedNeededSS = $speedNeeded - $speedSetBonus;
        $speedNeededWithSpeedArrowSS = $speedNeededWithSpeedArrow - $speedSetBonus;

        $this->line("Total speed needed from $baseSpeed: $speedNeeded :: $speedNeededWithSpeedArrow :: $speedNeededSS :: $speedNeededWithSpeedArrowSS");

        $averageSpeedNeeded = $speedNeeded / 6;
        $averageSpeedNeededWithSpeedArrow = $speedNeededWithSpeedArrow / 5;
        $averageSpeedNeededSS = $speedNeededSS / 6;
        $averageSpeedNeededWithSpeedArrowSS = $speedNeededWithSpeedArrowSS / 5;

        $this->line("Avg needed speeds: $averageSpeedNeeded :: $averageSpeedNeededWithSpeedArrow :: $averageSpeedNeededSS :: $averageSpeedNeededWithSpeedArrowSS");

        $preferredBigSet1 = collect($charRanking['sets'][0])->filter(function($rank) { return $rank > 0; })->keys();
        $preferredBigSet2 = collect($charRanking['sets'][1])->filter(function($rank) { return $rank > 0; })->keys();
        $preferredSmallSet = collect($charRanking['sets'][2])->filter(function($rank) { return $rank > 0; })->keys();

        $preferredSets = collect($preferredBigSet1)->concat($preferredBigSet2)->concat($preferredSmallSet)->unique();

        $acceptableNonSpeedArrowPrimaries = collect($charRanking['arrow'])->forget('UNITSTATSPEED')->filter(function($rank) { return $rank > 0; })->keys();

        $preferredModTable = $modLookup->only($preferredSets);

        $preferredModTable->flatten()->transform(function($mod) use ($charRanking) {
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

            foreach ($charRanking[$mod->slot] as $primary => $rank) {
                if ($rank > 0 && $mod->primary_type == $primary) {
                    $mod->score += $mod->pips * $rank / $charRanking['primaryTotal'][$mod->slot];
                    $mod->primary_score = $mod->pips * $rank / $charRanking['primaryTotal'][$mod->slot];
                }
            }

            foreach ($charRanking['secondary'] as $secondary => $rank) {
                if ($rank > 0 && isset($mod->{"${secondary}_score"})) {
                    $base = $mod->{"${secondary}_score"};

                    $ex = exp(($base / 100 * 4) - 4) * 100;

                    $mod->score += $ex;
                    $mod->{"secondary_score_${secondary}"} = $ex;
                }
            }

            return $mod;
        });

        $preferredModTable = $preferredModTable->mapWithKeys(function($slots, $set) {
            return [$set => $slots->mapWithKeys(function($mods, $slot) {
                return [$slot => $mods->sort(function($a, $b) {
                    return -($a->speed === $b->speed ? $a->score <=> $b->score : $a->speed <=> $b->speed);
                })->values()];
            })];
        });

        // $maxSpeeds = $preferredModTable->mapWithKeys(function($slots, $set) {
        //     return [$set => $slots->mapWithKeys(function($mods, $slot) {
        //         return [$slot => $mods->groupBy('speed')->sortKeys()->last()];
        //     })];
        // });

        $speeds = collect([]);
        $currentSet = collect([]);
        $setCount = 0;

        while ($speeds->count() < 6) {
            $usedMods = $speeds->pluck('uid');
            $usedSlots = $speeds->pluck('slot');
            $fastestMods = $preferredModTable
                ->filter(function($slots, $set) use ($currentSet) {
                    if (!$currentSet->empty()) {
                        return $set === $currentSet->get(0)->set;
                    }
                    return $set !== 'speed';
                })
                ->mapWithKeys(function($slots, $set) use ($usedMods, $usedSlots) {
                    return [
                        $set => $slots
                            ->flatten()
                            ->filter(function($mod) use ($usedMods, $usedSlots) {
                                return !$usedMods->contains($mod->uid) && !$usedSlots->contains($mod->slot) ;
                            })
                            ->groupBy('speed')
                            ->mapWithKeys(function($mods, $speed) {
                                return [$speed => $mods->sortBy('score')];
                            })
                    ];
                })
            ;

            $fastest = $fastestMods
                ->mapWithKeys(function($sortedModsBySpeed, $set) {
                    return [$set => $sortedModsBySpeed->keys()->max()];
                })
                ->sort()
                ->flipWithKeys()
            ;

            $topSet = collect($fastest->last())->sort(function($a, $b) use ($charRanking) {
                $aVal = $charRanking['sets'][0][$a] ?? $charRanking['sets'][1][$a] ?? $charRanking['sets'][2][$a];
                $bVal = $charRanking['sets'][0][$b] ?? $charRanking['sets'][1][$b] ?? $charRanking['sets'][2][$b];
                return $aVal <=> $bVal;
            })->first();
            $topSpeed = $fastest->keys()->last();
            $topMod = $fastestMods[$topSet]->get($topSpeed)->last();

            echo "{$topSet} :: {$topSpeed}\n";

            if (is_null($topMod)) {

            }

            $speeds->push($topMod);

            $setCount += 1;

            if ($setCount == $setCounts[$topSet]) {
                $setCount = 0;
                $currentSet = null;
            } else {
                $currentSet = $topSet;
            }

        }

        $speeds
            // ->only(['speed', 'slot', 'set', 'location', 'primary_type'])
            ->dd();

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
