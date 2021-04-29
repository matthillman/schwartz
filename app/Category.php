<?php

namespace App;

use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use Searchable;
    use \App\Database\CachesQueries;

    protected $fillable = [ 'category_id', 'description', 'visible' ];
    protected $appends = [ 'partition' ];

    public function getDescriptionAttribute($value) {
        return __("messages.$value");
    }

    public function getPartitionAttribute() {
        list($partition, ) = explode('_', $this->category_id);

        return $partition;
    }

    public static function visibleCategories() {
        return static::where('visible', 'true')->get()->groupBy('partition');
    }

    public function toSearchableArray()
    {
        $array = $this->toArray();

        unset($array['visible']);
        unset($array['created_at']);
        unset($array['updated_at']);

        return $array;
    }
}
