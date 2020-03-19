<?php

namespace App;

use ScoutElastic\Searchable;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use Searchable;
    use \App\Database\CachesQueries;

    protected $fillable = [ 'category_id', 'description', 'visible' ];
    protected $appends = [ 'partition' ];

    protected $indexConfigurator = Search\Indexes\CategoryIndexConfigurator::class;

    protected $searchRules = [
        Search\Rules\WildcardSearchRule::class,
    ];

    protected $mapping = [
        'properties' => [
            'description' => [
                'type' => 'text',
                'fields' => [
                    'raw' => [
                        'type' => 'keyword',
                    ],
                    'english' => [
                      'type' => 'text',
                      'analyzer' => 'english',
                    ],
                ]
            ],
            'category_id' => [
                'type' => 'text',
                'fields' => [
                    'raw' => [
                        'type' => 'keyword',
                    ],
                ]
            ],
            'partition' => [
                'type' => 'text',
                'fields' => [
                    'raw' => [
                        'type' => 'keyword',
                    ],
                ]
            ],
            'visible' => [
                'type' => 'boolean',
            ],
        ]
    ];

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
