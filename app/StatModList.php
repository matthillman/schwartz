<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StatModList extends Model
{
    use \App\Database\CachesQueries;

    protected $table = 'stat_mod_list';

    protected $fillable = ['id'];
}
