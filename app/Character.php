<?php

namespace App;

use App\Util\KeyStats;
use SwgohHelp\Enums\UnitStat;
use SwgohHelp\Enums\Alignment;
use Illuminate\Database\Eloquent\Model;

class Character extends Model
{
    use KeyStats;

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

    protected $appends = [ 'alignment', 'speed', 'is_ship', 'is_capital_ship', 'highlight_power', 'key_stats' ];

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
        return $this->keyStatsFor($this->unit_name);
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

    public function __get($key)
    {
        if (UnitStat::isValidKey($key)) {
            return $this->getAttribute('stats')['final'][UnitStat::$key()->getValue()] ?? 0;
        }
        return parent::__get($key);
    }

}
