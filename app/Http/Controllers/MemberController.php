<?php

namespace App\Http\Controllers;

use Gate;
use Artisan;
use App\Unit;
use App\Guild;
use App\Member;
use App\Category;
use App\Character;
use App\SquadGroup;
use App\AllyCodeMap;
use App\Jobs\ProcessUser;
use Illuminate\Http\Request;
use SwgohHelp\Enums\UnitStat;

use Illuminate\Database\Eloquent\ModelNotFoundException;

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

        return redirect()->route('members')->with('status', "Member add queued");
    }

    public function scrapeMember(Request $request, $id) {
        $member = Member::findOrFail($id);
        ProcessUser::dispatch($member->ally_code);

        return redirect()->route('members')->with('status', "Member scrape queued");
    }

    public function putScrapeMember(Request $request) {
        $validated = $request->validate([
            'id' => 'required',
        ]);

        $member = Member::findOrFail($validated['id']);
        ProcessUser::dispatch($member->ally_code);

        return response()->json([]);
    }

    public function scrapeAllyCodes(Request $request) {
        $members = collect(preg_split('/\r\n|\r|\n/', $request->get('members')))
            ->map(function($ally) {
                return Member::where(['ally_code' => str_replace('-', '', $ally)])->firstOrFail();
            })
            ->each(function($member) {
                ProcessUser::dispatch($member->ally_code);
            });

        return back()->withInput()->with('status', "Member scrapes queued for " . $members->count() . " ally codes");
    }

    public function show($allyCode) {
        $member = Member::with('characters.zetas')->where('ally_code', $allyCode)->firstOrFail();

        return view('member.profile', [
            'member' => $member,
            'units' => Unit::all(),
        ]);
    }

    public function mods($allyCode) {
        $member = Member::with('mods')->where('ally_code', $allyCode)->firstOrFail();

        return response()->json($member->setVisible(['player', 'guild_name', 'ally_code', 'mods']));
    }

    public function updateDiscordMapping(Request $request, $allyCode) {
        $validated = $request->validate([
            'value' => 'required',
        ]);

        $member = Member::with('characters.zetas')->where('ally_code', $allyCode)->firstOrFail();

        Gate::authorize('edit-guild-profile', $member->guild);

        $member->discord->discord_id = $validated['value'];
        $member->push();

        return response()->json(['success' => true]);
    }

    public function register(Request $request) {
        $validated = $request->validate([
            'ally_code' => 'required',
        ]);

        $allyCode = str_replace('-', '', $validated['ally_code']);

        $existing = AllyCodeMap::firstOrNew(['ally_code' => $allyCode]);

        if ($existing->exists) {
            return back()->withInput()->with('status-failed', "Ally Code $allyCode is already registered to another user");
        }

        $existing->discord_id = auth()->user()->discord_id;
        $existing->save();

        // the first call will at least ensure the profile is in the DB, even if they are not in a guild.
        Artisan::queue('swgoh:mods', [
            'user' => $allyCode
        ]);
        Artisan::queue('swgoh:guild', [
            '--ally' => true,
            'guild' => $allyCode
        ]);

        return back()->with('status', "Ally Code $allyCode registered successfully! Data is being updated now.");
    }

    public function characters(Request $request, $allyCode) {
        $member = Member::with('characters.zetas')->where('ally_code', $allyCode)->firstOrFail();
        $wantsChars = $request->route()->named('member.characters');

        $view = $wantsChars ? 'member.characters' : 'member.ships';
        $selectedCategory = Category::where('category_id', $request->category)->first();
        $units = $member->characters()->with('zetas')->where('combat_type', $wantsChars ? 1 : 2)->get()->filter(function($char) use ($selectedCategory) {
            return is_null($selectedCategory) || in_array($selectedCategory->category_id, $char->category_list);
        });

        return view($view, [
            'member' => $member,
            'units' => $units,
            'categories' => Category::visibleCategories(),
            'selected_category' => $selectedCategory,
        ]);
    }

    public function showCharacter($allyCode, $baseId) {
        $member = Member::with('characters.zetas')->where('ally_code', $allyCode)->firstOrFail();

        if (request_is_bot() && $member->stats->is_outdated) {
            throw new ModelNotFoundException;
        }

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
                    'Crit Damage' => 'UNITSTATCRITICALDAMAGE',
                    'Potency' => 'UNITSTATACCURACY',
                    'Tenacity' => 'UNITSTATRESISTANCE',
                    'Health Steal' => 'UNITSTATHEALTHSTEAL',
                    'Defense Penetration' => 'UNITSTATSHIELDPENETRATION',
                ],
            ],
            'stats_right' => [
                'Physical Offense' => [
                    'Damage' => 'UNITSTATATTACKDAMAGE',
                    'Crit Chance' => 'UNITSTATATTACKCRITICALRATING',
                    'Armor Penetration' => 'UNITSTATARMORPENETRATION',
                    'Accuracy' => 'UNITSTATDODGENEGATERATING',
                ],
                'Physical Survivability' => [
                    'Armor' => 'UNITSTATARMOR',
                    'Dodge Chance' => 'UNITSTATDODGERATING',
                    'Crit Avoidance' => 'UNITSTATATTACKCRITICALNEGATEPERCENTADDITIVE',
                ],
                'Special Offense' => [
                    'Damage' => 'UNITSTATABILITYPOWER',
                    'Crit Chance' => 'UNITSTATABILITYCRITICALRATING',
                    'Armor Penetration' => 'UNITSTATSUPPRESSIONPENETRATION',
                    'Accuracy' => 'UNITSTATDEFLECTIONNEGATERATING',
                ],
                'Special Survivability' => [
                    'Resistance' => 'UNITSTATSUPPRESSION',
                    'Dodge Chance' => 'UNITSTATDEFLECTIONRATING',
                    'Crit Avoidance' => 'UNITSTATABILITYCRITICALNEGATEPERCENTADDITIVE',
                ],
            ]
        ]);
    }

    public function listTeams($allyCode, $team) {
        $member = Member::with('characters.zetas')->where('ally_code', $allyCode)->firstOrFail();

        if (request_is_bot() && $member->stats->is_outdated) {
            throw new ModelNotFoundException;
        }

        if (ctype_digit(strval($team))) {
            $group = SquadGroup::find($team);
            if (is_null($group)) {
                abort(404);
            }
            $highlight = ($team == 29 || $team == 28) ? 'relic' : 'gear';
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
                if (starts_with($ally, 'g')) {
                    return Guild::where('guild_id', substr($ally, 1))->firstOrFail();
                }
                return Member::with(['stats','characters.zetas', 'guild'])->where(['ally_code' => str_replace('-', '', $ally)])->firstOrFail();
            })
            ->map(function($member) use ($compareUnits) {
                if ($member instanceof Guild) {
                    return $member->averageMember($compareUnits);
                }
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
                    'm1' => array_get($rFirst, 'relic_seven', 0) + array_get($rFirst, 'relic_six', 0) + array_get($rFirst, 'relic_five', 0),
                    'm2' => array_get($second, 'relic_seven', 0) + array_get($second, 'relic_six', 0) + array_get($second, 'relic_five', 0),
                ],
                'r_three_plus' => [
                    'm1' => array_get($rFirst, 'relic_seven', 0) + array_get($rFirst, 'relic_six', 0) + array_get($rFirst, 'relic_five', 0) + array_get($rFirst, 'relic_four', 0) + array_get($rFirst, 'relic_three', 0),
                    'm2' => array_get($second, 'relic_seven', 0) + array_get($second, 'relic_six', 0) + array_get($second, 'relic_five', 0) + array_get($second, 'relic_four', 0) + array_get($second, 'relic_three', 0),
                ],
                'r_all' => [
                    'm1' => array_get($rFirst, 'relic_seven', 0) + array_get($rFirst, 'relic_six', 0) + array_get($rFirst, 'relic_five', 0) + array_get($rFirst, 'relic_four', 0) + array_get($rFirst, 'relic_three', 0) + array_get($rFirst, 'relic_two', 0) + array_get($rFirst, 'relic_one', 0),
                    'm2' => array_get($second, 'relic_seven', 0) + array_get($second, 'relic_six', 0) + array_get($second, 'relic_five', 0) + array_get($second, 'relic_four', 0) + array_get($second, 'relic_three', 0) + array_get($second, 'relic_two', 0) + array_get($second, 'relic_one', 0),
                ],
                'g_total' => [
                    'm1' => array_get($gFirst, 'gear_thirteen', 0) + array_get($gFirst, 'gear_twelve', 0) + array_get($gFirst, 'gear_eleven', 0),
                    'm2' => array_get($second, 'gear_thirteen', 0) + array_get($second, 'gear_twelve', 0) + array_get($second, 'gear_eleven', 0),
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
