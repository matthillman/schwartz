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
                        return $this->statDisplayPair(UnitStat::$k());
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

    public function getStatRecommendationAttribute() {
        return $this->recommendationsFor($this->unit_name);
    }

    public function getStatGradeAttribute() {
        return $this->stat_recommendation->mapWithKeys(function ($levels, $key) {
            $value = $this->$key;
            $rankings = isset($levels['values']) ? $levels['values'] : $levels;
            $highest = collect($rankings)->reverse()->first(function ($v) use ($value) { return $v <= $value; });
            $rank = is_null($highest) ? 0 : (array_search($highest, $rankings) + 2);

            if (isset($levels['values']) && $rank > 0) {
                $related = collect($levels['related']);
                $member = $this->member;
                $rank = $related->keys()->reduce(function($rank, $unit) use ($related, $member, $key, $value) {
                    $rChar = $member->characters()->where('unit_name', $unit)->first();
                    if (is_null($rChar)) {
                        return 1;
                    }
                    $rStat = $rChar->$key;
                    $comparison = $related[$unit];
                    $rVal = $rStat;
                    if (is_array($comparison[1])) {
                        foreach ($comparison[1] as $compPair) {
                            $operator = $compPair[0];
                            $adjustment = $compPair[1];
                            $rVal = $this->adjustStat($rVal, $operator, $adjustment);
                        }
                    } else {
                        $operator = '+';
                        $adjustment = $comparison[1];
                        $rVal = $this->adjustStat($rStat, $operator, $adjustment);
                    }
                    $rVal = intval($rVal);

                    return $this->statCompare($value, $comparison[0], $rVal) ? $rank : 1;
                }, $rank);
            }

            return [UnitStat::$key()->getValue() => $rank];
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
