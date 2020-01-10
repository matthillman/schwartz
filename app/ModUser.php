<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ModUser extends Model
{
    protected $fillable = ['name'];
    public $timestamps = false;

    protected $dates = [
        'last_scrape',
    ];

    public function mods() {
        return $this->hasMany(Mod::class);
    }

    public function stats() {
        return $this->hasMany(ModStat::class);
    }

    public function member() {
        return $this->belongsTo(Member::class, 'name', 'ally_code');
    }

    public function hasChangesSince($date) {
        return is_null($this->last_scrape) || $date->greaterThan($this->last_scrape);
    }
}
