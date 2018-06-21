<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $fillable = ['url'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['guild_name'];

    public function characters() {
        return $this->hasMany(Character::class);
    }
    public function guild() {
        return $this->belongsTo(Guild::class);
    }

    public function getGuildNameAttribute() {
        return $this->guild->name;
    }
}
