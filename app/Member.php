<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $fillable = ['url', 'ally_code'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['guild_name'];

    public function characters() {
        return $this->hasMany(Character::class);
    }

    public function gear12() {
        return $this->characters()->where('gear_level', '=', 12);
    }

    public function gear13() {
        return $this->characters()->where('gear_level', '=', 13);
    }

    public function guild() {
        return $this->belongsTo(Guild::class)->withDefault();
    }

    public function getGuildNameAttribute() {
        return $this->guild->name;
    }
    public function getZetasAttribute() {
        return $this->characters->pluck('zetas')->flatten();
    }
}
