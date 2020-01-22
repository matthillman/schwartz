<?php

namespace App;

use App\Util\KeyStats;
use App\Util\RecommendsStats;
use SwgohHelp\Enums\UnitStat;
use SwgohHelp\Enums\Alignment;
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

    // public function getPowerAttribute($value) {
    //     static $relicBonus = [ 0, 759, 1594, 2505, 3492, 4554, 6072, 7969 ];

    //     if ($this->relic >= 2) {
    //         return $value + $relicBonus[$this->relic - 2];
    //     }

    //     return $value;
    // }
    public function getAlignmentAttribute() {
        return strtolower((new Alignment($this->unit->alignment))->getKey());
    }
    public function getDisplayNameAttribute() {
        return $this->unit->name;
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

    public function __get($key)
    {
        if (UnitStat::isValidKey($key)) {
            return $this->getAttribute('stats')['final'][UnitStat::$key()->getValue()] ?? 0;
        }
        return parent::__get($key);
    }

}
