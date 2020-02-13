<?php

namespace App\Http\Controllers;

use App\Unit;
use App\Member;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    use Util\Squads;

    public function show($allyCode) {
        $member = Member::with('characters.zetas')->where('ally_code', $allyCode)->firstOrFail();

        return view('member.profile', [
            'member' => $member,
            'units' => Unit::all(),
        ]);
    }

    public function listTeams($allyCode, $team) {
        $member = Member::with('characters.zetas')->where('ally_code', $allyCode)->firstOrFail();

        list($highlight, $teams) = $this->getSquadsFor($team);

        return view("member.teams", [
            'member' => $member,
            'units' => Unit::all(),
            'teams' => $teams,
            'highlight' => $highlight,
            'team' => $team,
        ]);
    }

    public function compareMembers() {
        $members = collect(explode(',', request()->get('members')))
            ->map(function($ally) {
                return Member::with(['stats','characters.zetas', 'guild'])->where(['ally_code' => $ally])->firstOrFail();
            })
            ->map(function($member) {
                return $member->toCompareData();
            })
        ;
        $winners = $members->first()->keys()->mapWithKeys(function($key) use ($members) {
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

        return view('member.compare', [
            'character_list' => Member::getCompareCharacters()->merge(Member::getKeyCharacters()),
            'ship_list' => Member::getKeyShips(),
            'stat_list' => Member::getCompareStats(),
            'data' => $members->keyBy('id'),
            'winner' => $winners,
        ]);
    }
}
