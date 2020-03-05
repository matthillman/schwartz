<?php

namespace App\Http\Controllers;

use App\Unit;
use App\Member;
use App\Category;
use App\Character;
use App\SquadGroup;
use App\Jobs\ProcessUser;
use Illuminate\Http\Request;
use SwgohHelp\Enums\UnitStat;

class MemberController extends Controller
{
    use Util\Squads;

    public function index() {
        return view('members');
    }

    public function compare(Request $request) {
        $members = array_map('trim', explode("\n", $request->members));

        return redirect()->route('member.compare', ['members' => implode(',', $members), 'units' => $request->get('units', '')]);
    }

    public function addMember(Request $request) {
        ProcessUser::dispatch($request->member);

        return redirect()->route('members')->with('memberStatus', "Member add queued");
    }

    public function scrapeMember(Request $request, $id) {
        $member = Member::findOrFail($id);
        ProcessUser::dispatch($member->ally_code);

        return redirect()->route('members')->with('memberStatus', "Member scrape queued");
    }

    public function show($allyCode) {
        $member = Member::with('characters.zetas')->where('ally_code', $allyCode)->firstOrFail();

        return view('member.profile', [
            'member' => $member,
            'units' => Unit::all(),
        ]);
    }

    public function characters(Request $request, $allyCode) {
        $member = Member::with('characters.zetas')->where('ally_code', $allyCode)->firstOrFail();

        return view('member.characters', [
            'member' => $member,
            'categories' => Category::visibleCategories(),
            'selected_category' => Category::where('category_id', $request->category)->first(),
        ]);
    }

    public function showCharacter($allyCode, $baseId) {
        $member = Member::with('characters.zetas')->where('ally_code', $allyCode)->firstOrFail();

        return view('member.character', [
            'member' => $member,
            'character' => $member->characters()->with('zetas')->where('unit_name', $baseId)->firstOrFail(),
            'stats_left' => [
                'Base Attributes' => [
                    'Strength (STR)' => 'UNITSTATSTRENGTH',
                    'Agility (AGI)' => 'UNITSTATAGILITY',
                    'Tactics (TAC)' => 'UNITSTATINTELLIGENCE',

                    // Strength Growth (STR)
                    // 21.8
                    // Agility Growth (AGI)
                    // 18.6
                    // Tactics Growth (TAC)
                    // 17.6
                    'Mastery' => 'UNITSTATMASTERY',
                ],
                'General' => [
                    'Health' => 'UNITSTATMAXHEALTH',
                    'Protection' => 'UNITSTATMAXSHIELD',
                    'Speed' => 'UNITSTATSPEED',
                    'Critical Damage' => 'UNITSTATCRITICALDAMAGE',
                    'Potency' => 'UNITSTATACCURACY',
                    'Tenacity' => 'UNITSTATRESISTANCE',
                    'Health Steal' => 'UNITSTATHEALTHSTEAL',
                    'Defense Penetration' => 'UNITSTATSHIELDPENETRATION',
                ],
            ],
            'stats_right' => [
                'Physical Offense' => [
                    'Damage' => 'UNITSTATATTACKDAMAGE',
                    'Critical Chance' => 'UNITSTATATTACKCRITICALRATING',
                    'Armor Penetration' => 'UNITSTATARMORPENETRATION',
                    'Accuracy' => 'UNITSTATDODGENEGATERATING',
                ],
                'Physical Survivability' => [
                    'Armor' => 'UNITSTATARMOR',
                    'Dodge Chance' => 'UNITSTATDODGERATING',
                    'Critical Avoidance' => 'UNITSTATATTACKCRITICALNEGATERATING',
                ],
                'Special Offense' => [
                    'Damage' => 'UNITSTATABILITYPOWER',
                    'Critical Chance' => 'UNITSTATABILITYCRITICALRATING',
                    'Armor Penetration' => 'UNITSTATSUPPRESSIONPENETRATION',
                    'Accuracy' => 'UNITSTATDEFLECTIONNEGATERATING',
                ],
                'Special Survivability' => [
                    'Armor' => 'UNITSTATSUPPRESSION',
                    'Dodge Chance' => 'UNITSTATDEFLECTIONRATING',
                    'Critical Avoidance' => 'UNITSTATABILITYCRITICALNEGATERATING',
                ],
            ]
        ]);
    }

    public function listTeams($allyCode, $team) {
        $member = Member::with('characters.zetas')->where('ally_code', $allyCode)->firstOrFail();

        if (ctype_digit(strval($team))) {
            $group = SquadGroup::findOrFail($team);
            $highlight = 'gear';
            $teams = $group->squads->mapWithKeys(function($squad) {
                return [$squad->display => collect([$squad->leader_id])->concat($squad->additional_members)->toArray()];
            });
        } else {
            list($highlight, $teams) = $this->getSquadsFor($team);
        }

        return view("member.teams", [
            'member' => $member,
            'units' => Unit::all(),
            'teams' => $teams,
            'highlight' => $highlight,
            'team' => $team,
        ]);
    }

    public function characterMods($character) {
        $c = Character::with(['member', 'unit', 'mods'])->findOrFail($character);

        return view('member.character_mods', [
            'character' => $c,
            'attributes' => [
                'speed' => UnitStat::UNITSTATSPEED(),
                'tenacity' => UnitStat::UNITSTATRESISTANCE(),
                'physical' => UnitStat::UNITSTATATTACKDAMAGE(),
                'health' => UnitStat::UNITSTATMAXHEALTH(),
                'special' => UnitStat::UNITSTATABILITYPOWER(),
                'protection' => UnitStat::UNITSTATMAXSHIELD(),
                'critical chance' => 'UNITSTATCRITICALCHANCEPERCENTADDITIVE',
                'offense' => 'UNITSTATOFFENSE',
                'defense' => 'UNITSTATDEFENSE',
            ],
        ]);
    }

    public function compareMembers(Request $request) {
        $compareUnits = array_filter(explode(',', $request->get('units', '')));

        $members = collect(explode(',', $request->get('members')))
            ->map(function($ally) {
                return Member::with(['stats','characters.zetas', 'guild'])->where(['ally_code' => str_replace('-', '', $ally)])->firstOrFail();
            })
            ->map(function($member) use ($compareUnits) {
                return $member->toCompareData($compareUnits);
            })
        ;
        $keys = $members->map(function($m) { return $m->keys(); })
            ->reduce(function($m1, $m2) {
                return $m1->merge($m2);
            }, collect())
            ->unique();

        $winners = $keys->mapWithKeys(function($key) use ($members) {
            return [$key => $members->reduce(function($first, $second) use ($key) {
                $firstValue = $first->count() ? $first->first()->get($key) : null;
                $secondValue = $second->get($key);
                // Things where lower is better
                if (in_array($key, ['squad_rank', 'fleet_rank'])) {
                    $firstValue = $secondValue;
                    $secondValue = $first->count() ? $first->first()->get($key) : PHP_INT_MAX;
                }

                if ($secondValue > $firstValue) {
                    return collect([$second]);
                } else if (is_int($secondValue) && $firstValue == $secondValue && $secondValue > 0) {
                    $first->push($second);
                }
                return $first;
            }, collect([]))->pluck('id')];
        });

        $totalWinners = $members->reduce(function($first, $second) {
            if (is_null($first)) {
                return [
                    'r_total' => collect([$second]),
                    'r_three_plus' => collect([$second]),
                    'r_all'   => collect([$second]),
                    'g_total' => collect([$second]),
                ];
            }

            $rFirst = $first['r_total']->first();
            $gFirst = $first['g_total']->first();
            $totals = [
                'r_total' => [
                    'm1' => $rFirst['relic_seven'] + $rFirst['relic_six'] + $rFirst['relic_five'],
                    'm2' => $second['relic_seven'] + $second['relic_six'] + $second['relic_five'],
                ],
                'r_three_plus' => [
                    'm1' => $rFirst['relic_seven'] + $rFirst['relic_six'] + $rFirst['relic_five'] + $rFirst['relic_four'] + $rFirst['relic_three'],
                    'm2' => $second['relic_seven'] + $second['relic_six'] + $second['relic_five'] + $second['relic_four'] + $second['relic_three'],
                ],
                'r_all' => [
                    'm1' => $rFirst['relic_seven'] + $rFirst['relic_six'] + $rFirst['relic_five'] + $rFirst['relic_four'] + $rFirst['relic_three'] + $rFirst['relic_two'] + $rFirst['relic_one'],
                    'm2' => $second['relic_seven'] + $second['relic_six'] + $second['relic_five'] + $second['relic_four'] + $second['relic_three'] + $second['relic_two'] + $second['relic_one'],
                ],
                'g_total' => [
                    'm1' => $gFirst['gear_thirteen'] + $gFirst['gear_twelve'] + $gFirst['gear_eleven'],
                    'm2' => $second['gear_thirteen'] + $second['gear_twelve'] + $second['gear_eleven'],
                ],
            ];

            $r = [];
            foreach ($totals as $key => $counts) {
                $m1Total = $counts['m1'];
                $m2Total = $counts['m2'];
                if ($m1Total > $m2Total) {
                    $r[$key] = $first[$key];
                } else if ($m2Total > $m1Total) {
                    $r[$key] =  collect([$second]);
                } else {
                    $first[$key]->push($second);
                    $r[$key] = $first[$key];
                }
            }

            return $r;
        });

        $winners['r_total'] = $totalWinners['r_total']->pluck('id');
        $winners['r_three_plus'] = $totalWinners['r_three_plus']->pluck('id');
        $winners['r_all'] = $totalWinners['r_all']->pluck('id');
        $winners['g_total'] = $totalWinners['g_total']->pluck('id');

        if (empty($compareUnits)) {
            $charUnits = Member::getCompareCharacters()->merge(Member::getKeyCharacters());
            $shipUnits = Member::getKeyShips();
        } else {
            list($charUnits, $shipUnits) = collect($compareUnits)
                ->map(function($base) {
                    $unit = Unit::where('base_id', $base)->firstOrFail();
                    return [
                        'base_id' => $unit->base_id,
                        'name' => $unit->short_name,
                        'alignment' => $unit->alignment,
                        'combat_type' => intval($unit->combat_type),
                    ];
                })
                ->partition(function ($unit) {
                    return $unit['combat_type'] == 1;
                });
            ;
            $charUnits = $charUnits->mapWithKeys(function($u) { return [$u['base_id'] => [ 'name' => $u['name'], 'alignment' => $u['alignment'] ] ];});
            $shipUnits = $shipUnits->mapWithKeys(function($u) { return [$u['base_id'] => [ 'name' => $u['name'], 'alignment' => $u['alignment'] ] ];});
        }

        return view('member.compare', [
            'character_list' => $charUnits,
            'ship_list' => $shipUnits,
            'stat_list' => Member::getCompareStats(),
            'data' => $members->keyBy('id'),
            'winner' => $winners,
        ]);
    }
}
