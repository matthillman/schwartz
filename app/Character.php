<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Character extends Model
{
    protected $fillable = ['member_id', 'unit_name'];

    public function member() {
        return $this->belongsTo(Member::class);
    }
}
