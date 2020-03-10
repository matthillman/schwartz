<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SquadGroup extends Model
{
    public function squads() {
        return $this->hasMany(Squad::class);
    }

    public function guild() {
        return $this->belongsTo(Guild::class);
    }

    public function plans() {
        return $this->hasMany(TerritoryWarPlan::class);
    }

    public function scopeGlobal($query) {
        return $query->where('guild_id', 0)->where('publish', true);
    }
    public function scopeForGuild($query, $guild) {
        if (!($guild instanceof Guild)) {
            $guild = Guild::findOrFail($guild);
        }
        return $query->where('guild_id', $guild->id)->where('publish', true);
    }
}
