<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = ['base_id'];

    protected $casts = [
        'crew_list' => 'array',
    ];


    public function preference() {
        return $this->hasOne(UnitModPreference::class, 'unit_id', 'base_id');
    }
    public function getNameAttribute($value) {
        return __('messages.'.$value);
    }
}
