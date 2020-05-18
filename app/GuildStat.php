<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GuildStat extends Model
{
    protected $casts = [
        'unit_data' => 'collection',
        'mod_data' => 'collection',
    ];
}
