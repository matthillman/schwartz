<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CharacterZeta extends Model
{
    protected $table = 'character_zeta';
    protected $fillable = ['zeta_id', 'character_id'];
}
