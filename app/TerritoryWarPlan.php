<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TerritoryWarPlan extends Model
{
    protected $casts = [
        'zone_1' => 'array',
        'zone_2' => 'array',
        'zone_3' => 'array',
        'zone_4' => 'array',
        'zone_5' => 'array',
        'zone_6' => 'array',
        'zone_7' => 'array',
        'zone_8' => 'array',
        'zone_9' => 'array',
        'zone_10' => 'array',
    ];

    public function guild() {
        return $this->belongsTo(Guild::class);
    }
    public function squad_group() {
        return $this->belongsTo(SquadGroup::class);
    }
}
