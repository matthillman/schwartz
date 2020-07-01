<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class MemberStatistic extends Model
{
    protected $casts = [
        'data' => 'collection',
    ];

    public function getIsOutdatedAttribute() {
        return Carbon::now()->diffInMinutes($this->updated_at) > config('swgoh.max_age');
    }
}
