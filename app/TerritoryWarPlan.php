<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TerritoryWarPlan extends Model
{
    protected $casts = [
        'zone_1' => 'collection',
        'zone_2' => 'collection',
        'zone_3' => 'collection',
        'zone_4' => 'collection',
        'zone_5' => 'collection',
        'zone_6' => 'collection',
        'zone_7' => 'collection',
        'zone_8' => 'collection',
        'zone_9' => 'collection',
        'zone_10' => 'collection',
    ];

    public function guild() {
        return $this->belongsTo(Guild::class);
    }
    public function squad_group() {
        return $this->belongsTo(SquadGroup::class);
    }

    public function jsonSerialize() {
        $data = $this->toArray();

        foreach ($this->casts as $attribute => $type) {
            $data[$attribute] = (object) $data[$attribute];
        }

        return $data;
    }
}
