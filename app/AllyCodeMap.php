<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AllyCodeMap extends Model
{
    protected $table = 'ally_code_map';

    protected $fillable = [ 'discord_id', 'server_id', 'ally_code' ];
}
