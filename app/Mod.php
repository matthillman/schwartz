<?php

namespace App;

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
    ];

    protected $hidden = [
        'primary_type', 'primary_value',
        'secondary_1_type', 'secondary_1_value',
        'secondary_2_type', 'secondary_2_value',
        'secondary_3_type', 'secondary_3_value',
        'secondary_4_type', 'secondary_4_value',
    ];
    protected $appends = ['primary', 'secondaries'];

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

        $json['location'] = $char->name;

        return $json;
    }
}
