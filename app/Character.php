<?php

namespace App;

use App\Util\GameData;
use App\Util\KeyStats;
use App\Util\RecommendsStats;
use SwgohHelp\Enums\UnitStat;
use SwgohHelp\Enums\Alignment;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;

class Character extends Model
{
    use KeyStats;
    use RecommendsStats;

    public static $inSquadID = null;

    protected $fillable = [
        'member_id',
        'unit_name',
        'gear_level',
        'power',
        'level',
        'combat_type',
        'rarity',
        'stats',
        'relic',
        'raw',
    ];

    protected $appends = [
        'alignment',
        'speed',
        'is_ship',
        'is_capital_ship',
        'highlight_power',
        'key_stats',
        'stat_grade',
        'base_speed',
        'display_name',
    ];

    protected $casts = [
        'stats' => 'array',
    ];

    protected $hidden = [ 'raw' ];

    public function member() {
        return $this->belongsTo(Member::class);
    }
    public function zetas() {
        return $this->belongsToMany(Zeta::class)->withTimestamps();
    }
    public function unit() {
        return $this->belongsTo(Unit::class, 'unit_name', 'base_id');
    }
    public function mods() {
        return $this->hasMany(CharacterMod::class);
    }

    public function rawData() {
        return $this->hasOne(CharactersRaw::class);
    }

    public function getAlignmentAttribute() {
        return $this->unit->alignment;
    }
    public function getDisplayNameAttribute() {
        return $this->unit->name;
    }
    public function getCategoryListAttribute() {
        return $this->unit->category_list;
    }
    public function getSpeedAttribute() {
        return $this->UNITSTATSPEED;
    }
    public function getBaseSpeedAttribute() {
        return $this->baseAttribute(UnitStat::UNITSTATSPEED());
    }
    public function baseAttribute(UnitStat $stat) {
        $stats = $this->getAttribute('stats');
        $finalStat = array_get($stats, 'final.'.$stat->getValue(), 0);
        $modBonuses = array_get($stats, 'mods.'.$stat->getValue(), 0);

        return $finalStat - $modBonuses;
    }
    public function modBonus($stat) {
        if (is_string($stat)) {
            $stat = UnitStat::$stat();
        }
        return array_get($this->getAttribute('stats'), 'mods.'.$stat->getValue(), null);
    }
    public function modTotal($stat) {
        if ($stat instanceof UnitStat) {
            $total = $this->modBonus($stat) ?: 0;
            $isSpeed = UnitStat::UNITSTATSPEED()->equals($stat);
        } else {
            $total = $this->mods->reduce(function ($total, $mod) use ($stat) {
                return $total + array_get($mod->secondaries, $stat, 0);
            }, 0);
            $isSpeed = $stat == 'UNITSTATSPEED';
        }

        $speedSetCount = $this->mods->countBy(function($mod) {
            return $mod->set;
        })->get('speed');

        if ($speedSetCount >= 4 && $isSpeed){
            $total = "$total (+10%)";
        }

        if ($stat == 'UNITSTATCRITICALCHANCEPERCENTADDITIVE') {
            $total = $total / 100;
        }

        return $total;
    }
    public function getKeyStatsAttribute() {
        return $this->keyStatsFor($this->unit_name)
            ->merge(
                $this->stat_recommendation->keys()
                    ->mapWithKeys(function($k) {
                        return $this->statDisplayPair(new UnitStat($k));
                    })
            )->mapWithKeys(function($item, $key) {
                return [UnitStat::$key()->getValue() => $item];
            });
    }
    public function getIsShipAttribute() {
        return $this->combat_type !== 1;
    }
    public function getIsCharAttribute() {
        return $this->combat_type == 1;
    }
    public function getIsCapitalShipAttribute() {
        return $this->is_ship && starts_with($this->unit_name, 'CAPITAL');
    }
    public function getHighlightPowerAttribute() {
        if ($this->is_ship) {
            return $this->power >= 40000 ? 6 : 0;
        }

        if ($this->power >= 23000) {
            return 6;
        }

        if ($this->power >= 22000) {
            return 5;
        }

        if ($this->power >= 21000) {
            return 4;
        }

        if ($this->power >= 17700) {
            return 3;
        }

        if ($this->power >= 17500) {
            return 2;
        }

        if ($this->power >= 16500) {
            return 1;
        }

        return 0;
    }

    public function getInSquadAttribute() {
        return static::$inSquadID ? Squad::findOrFail(static::$inSquadID) : new Squad;
    }

    public function getStatRecommendationAttribute() {
        return $this->in_squad->stats ? collect($this->in_squad->stats->get($this->unit_name)) : collect();
    }

    public function getStatGradeAttribute() {
        return $this->stat_recommendation->mapWithKeys(function ($recommendations, $key) {
            $unitStat = new UnitStat($key);
            $value = $this->{$unitStat->getKey()};
            $rankings = array_get($recommendations, 'tier', []);
            // Get the value of the highest tier our stat passes
            $highest = collect($rankings)->first(function ($v) use ($value) { return $v <= $value; });
            // get the index of that tier
            $rankings = array_reverse($rankings);
            $rank = is_null($highest) ? 0 : (array_search($highest, $rankings) + 2);

            // Comparisons only matter if we aren't already failing
            if (isset($recommendations['related']) && $rank > 0) {
                $related = collect($recommendations['related']);
                $member = $this->member;
                $relatedUnits = $this->member->characters()->whereIn('unit_name', $related->keys())->get();
                $rank = $relatedUnits->reduce(function($rank, $unit) use ($related, $unitStat, $value, $key) {
                    $relatedBaseStat = $unit->{$unitStat->getKey()};
                    $function = $related[$unit->unit_name];

                    // Replace all references to the related unit with the actual base stat number
                    $function = str_replace($unit->unit_name, $relatedBaseStat, $function);
                    // Replaces references to us with the variable x so that it's an equation
                    // the solver can handle
                    $function = str_replace($this->unit_name, 'x', $function);

                    $operator = [];
                    if (preg_match('/(<[^=]|>[^=]|>=|<=)/', $function, $operator)) {
                        $function = preg_replace('/(<[^=]|>[^=]|>=|<=)/', '=', $function);
                    }

                    $target = solve($function);

                    if (!$this->isStatPercent($key)) {
                        $target = floor($target);
                    }

                    $comparison = head($operator) ?: '=';
                    $left = $target;
                    $right = $value;
                    if ($comparison != '=') {
                        list($lhs, ) = explode('=', $function);
                        if (str_contains($lhs, 'x')) {
                            $left = $value;
                            $right = $target;
                        }
                    }

                    return $this->statCompare($left, $comparison, $right) ? $rank : 1;
                }, $rank);
            }

            return [$key => $rank];
        });
    }

    public function getCrewDisplayListAttribute() {
        $ourSkills = collect($this->rawData->data['skillList'])->keyBy('id');
        return $this->unit->crew_list->map(function($crew) use ($ourSkills) {
            $id = $crew['skillReferenceList'][0]['skillId'];
            $skill = static::displaySkill($ourSkills[$id] ?? [ 'id' => $id, 'tier' => -1 ]);
            $skill->put('character', $this->member->characters()->where('unit_name', $crew['unitId'])->first());
            return $skill;
        });
    }

    public function getSkillListAttribute() {
        $skillList = $this->unit->skills->pluck('skillId');
        $ourSkills = collect($this->rawData->data['skillList'])->keyBy('id');

        return $skillList->map(function($skill) use ($ourSkills) {
            return collect([
                'id' => $skill,
                'tier' => $ourSkills->get($skill)['tier'] ?? -1,
            ]);
        });
    }

    public function getAllSkillsAttribute() {
        $skillList = $this->unit->skills->concat($this->unit->crew_list->pluck('skillReferenceList')->flatten(1))->pluck('skillId');
        $ourSkills = collect($this->rawData->data['skillList'])->keyBy('id');

        return $skillList->map(function($skill) use ($ourSkills) {
            return collect([
                'id' => $skill,
                'tier' => $ourSkills->get($skill)['tier'] ?? -1,
            ]);
        });
    }

    private static function displaySkill($skill) {
        $skills = GameData::skills();
        $recipes = GameData::recipes();
        $materials = GameData::materials();
        $abilities = GameData::abilities();

        $skill = Collection::wrap($skill);

        $skillDef = $skills->get($skill->get('id'));

        $ability = $abilities->get($skillDef['abilityReference']);
        $skill['name'] = __('messages.' . $ability['nameKey']);
        $skill['description'] = preg_replace('/\[-\]\[\/c\]/', '</span>',
            preg_replace('/\[c\]\[([0-9A-Fa-f]{6})\]/', '<span :style="{color: `#$1`}">', __('messages.' . $ability['descKey']))
        );

        if ($skill->get('tier') >= 0) {
            $tier = $skillDef['tierList'][$skill->get('tier')];

            if ($skill->get('tier') == count($skillDef['tierList']) - 1) {
                $recipe = $recipes->get($tier['recipeId']);
                $currentTier = 0;

                foreach ($recipe['ingredientsList'] as $ingredient) {
                    $material = $materials->get($ingredient['id']);
                    if (!is_null($material) && $material['tier'] > $currentTier) {
                        $skill['image'] = $material['iconKey'];
                        $currentTier = $material['tier'];
                    }
                }
            }
        }

        return $skill;
    }

    public function getSkillDisplayListAttribute() {
        return $this->skill_list->map(function($list) {
            return static::displaySkill($list);
        });
    }

    public static function materialsNeededForSkills(Collection $skillList) {
        if (!$skillList->first()->has('id')) {
            $skillList = $skillList->flatten(1);
        }

        $skills = GameData::skills();
        $recipes = GameData::recipes();
        $materials = GameData::materials();

        $totals = [];
        foreach ($skillList as $skill) {
            $skillDef = $skills->get($skill->get('id'));
            $tiers = collect($skillDef['tierList']);
            $thisTier = $skill->get('tier', -1);
            if ($thisTier < $tiers->count()) {
                foreach ($tiers->slice($thisTier + 1) as $tier) {
                    $recipe = $recipes->get($tier['recipeId']);

                    foreach ($recipe['ingredientsList'] as $ingredient) {
                        $key = $materials->get($ingredient['id'])['iconKey'] ?? $ingredient['id'];
                        $totals[$key] = ($totals[$key] ?? 0) + $ingredient['maxQuantity'];
                    }
                }
            }
        }

        return $totals;
    }

    public function getAbilityMaterialsNeededAttribute() {
        return static::materialsNeededForSkills($this->all_skills);
    }

    public function __get($key)
    {
        if (UnitStat::isValidKey($key)) {
            return $this->getAttribute('stats')['final'][UnitStat::$key()->getValue()] ?? 0;
        }
        return parent::__get($key);
    }

}
