<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TerritoryWarTeam extends Model
{
    protected $fillable = ['name'];
    
    public function counters() {
        return $this->hasMany(TerritoryWarTeamCounter::class);
    }
}
