<?php

namespace App;

use SwgohHelp\Enums\Alignment;
use Illuminate\Database\Eloquent\Model;

class Character extends Model
{
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

    protected $appends = [ 'alignment' ];

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
    public function getAlignmentAttribute() {
        return strtolower((new Alignment($this->unit->alignment))->getKey());
    }
}
