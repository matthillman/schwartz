<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UnitModPreference extends Model
{
    protected $fillable = ['unit_id'];

    public function unit() {
        return $this->belongsTo(Unit::class, 'unit_id', 'base_id');
    }
}
