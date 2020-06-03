<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Squad extends Model
{
    protected $fillable = ['leader_id'];

    protected $casts = [
        'additional_members' => 'array',
        'stats' => 'collection',
    ];

    public function group() {
        return $this->belongsTo(SquadGroup::class);
    }

    public function getOtherMembersAttribute() {
        return explode(',', $this->additional_members);
    }
}
