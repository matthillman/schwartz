<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Squad extends Model
{
    protected $fillable = ['leader_id'];

    protected $casts = [
        'additional_members' => 'array',
    ];
}
