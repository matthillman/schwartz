<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Guild extends Model
{
    protected $fillable = ['guild_id'];

    public function members() {
        return $this->hasMany(Member::class);
    }
}
