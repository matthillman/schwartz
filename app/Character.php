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
    ];

    protected $appends = [ 'alignment', 'speed', 'is_ship', 'is_capital_ship', 'highlight_power', 'key_stats', 'stat_grade' ];

    protected $casts = [
        'stats' => 'array'
    ];

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
        return $this->member->mods()->where('location', $this->unit_name);
    }
    public function getAlignmentAttribute() {
        return strtolower((new Alignment($this->unit->alignment))->getKey());
    }
    public function getSpeedAttribute() {
        return $this->UNITSTATSPEED;
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
            return $this->power >= 40000 ? 3 : 0;
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
            $rank = 0;

            if (isset($levels['values'])) {
                $highest = collect($levels['values'])->reverse()->first(function ($v) use ($value) { return $v <= $value; });
                $rank = is_null($highest) ? 0 : (array_search($highest, $levels) + 2);

                if ($rank > 0) {
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
                        return $this->statCompare($value, $comparison[0], $rVal) ? $rank : 1;
                    }, $rank);
                }
            } else {
                $highest = collect($levels)->reverse()->first(function ($v) use ($value) { return $v <= $value; });
                $rank = is_null($highest) ? 0 : (array_search($highest, $levels) + 2);
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
