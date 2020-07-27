<?php

namespace App;

use SwgohHelp\Enums\UnitStat;
use Illuminate\Database\Eloquent\Model;

class Mod extends Model
{
    protected $fillable = [
        'uid',
        'name',
        'location',
        'slot',
        'set',
        'pips',
        'level',

        'primary',
        'secondaries',
        'raw',
    ];

    protected $hidden = [
        'primary_type', 'primary_value',
        'secondary_1_type', 'secondary_1_value',
        'secondary_2_type', 'secondary_2_value',
        'secondary_3_type', 'secondary_3_value',
        'secondary_4_type', 'secondary_4_value',
        'raw',
    ];
    protected $appends = ['primary', 'secondaries', 'rolls'];

    protected $casts = [
        'raw' => 'array',
    ];

    public function getPrimaryAttribute($value) {
        return [
            'type' => $this->primary_type,
            'value' => $this->primary_value,
        ];
    }

    public function getSecondariesAttribute($value) {
        $secondaries = [];

        foreach ([1, 2, 3, 4] as $index) {
            if (!is_null($this->{"secondary_{$index}_type"})) {
                $secondaries[$this->{"secondary_{$index}_type"}] = $this->{"secondary_{$index}_value"};
            }
        }

        return $secondaries;
    }

    public function getRollsAttribute() {
        $rolls = [];

        if (isset($this->raw['secondaryStatList'])) {
            foreach ($this->raw['secondaryStatList'] as $secondary) {
                $rolls[(new UnitStat($secondary['stat']['unitStatId']))->getKey()] = $secondary['statRolls'];
            }
        }

        return $rolls;
    }

    public function setPrimaryAttribute($value) {
        $this->attributes['primary_type'] = $value['type'];
        $this->attributes['primary_value'] = $value['value'];
    }

    public function setSecondariesAttribute($value) {
        foreach (collect($value)->keys() as $index => $key) {
            $fixed = $index + 1;
            $this->attributes["secondary_{$fixed}_type"] = $key;
            $this->attributes["secondary_{$fixed}_value"] = $value[$key]['value'];
        }
    }

    public function user() {
        return $this->belongsTo(ModUser::class, 'mod_user_id');
    }

    public function toArray() {
        $json = parent::toArray();

        $char = Unit::where('base_id', $json['location'])->first();

        $json['location_id'] = $json['location'];
        $json['location_alignment'] = $char ? $char->alignment : 'neutral';
        $json['location'] = $char ? $char->name : null;

        return $json;
    }
}
