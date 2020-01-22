<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MembersRaw extends Model
{
    protected $table = 'members_raw';
    protected $fillable = ['member_id', 'data'];

    protected $casts = [
        'data' => 'array',
    ];
    protected $primaryKey = 'member_id';
    public $timestamps = false;
}
