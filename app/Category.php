<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [ 'category_id', 'description', 'visible' ];

    public function getDescriptionAttribute($value) {
        return __("messages.$value");
    }

    public function getPartitionAttribute() {
        list($partition, ) = explode('_', $this->category_id);

        return $partition;
    }

    public static function visibleCategories() {
        return static::where('visible', true)->get()->groupBy('partition');
    }
}
