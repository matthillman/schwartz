<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CharactersRaw extends Model
{
    protected $table = 'characters_raw';
    protected $fillable = ['character_id', 'data'];
    protected $casts = [
        'data' => 'array',
    ];
    protected $primaryKey = 'character_id';
    public $timestamps = false;
}
