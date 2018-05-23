<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $fillable = ['url'];

    public function characters() {
        return $this->hasMany(Character::class);
    }
    public function guild() {
        return $this->belongsTo(Guild::class);
    }
}
