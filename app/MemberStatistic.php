<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MemberStatistic extends Model
{
    protected $casts = [
        'data' => 'collection',
    ];
}
