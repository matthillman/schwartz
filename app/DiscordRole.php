<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DiscordRole extends Model
{
    protected $casts = [
        'roles' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'discord_id', 'discord_id');
    }
}
