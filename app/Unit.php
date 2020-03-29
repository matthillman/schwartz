<?php

namespace App;

use ScoutElastic\Searchable;
use SwgohHelp\Enums\Alignment;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use Searchable;
    use \App\Database\CachesQueries;

    protected $fillable = ['base_id'];

    protected $casts = [
        'crew_list' => 'collection',
        'category_list' => 'array',
        'skills' => 'collection',
    ];

    protected $indexConfigurator = Search\Indexes\UnitIndexConfigurator::class;

    protected $searchRules = [
        Search\Rules\WildcardSearchRule::class,
    ];

    protected $mapping = [
        'properties' => [
            'base_id' => [
                'type' => 'text',
                'fields' => [
                    'raw' => [
                        'type' => 'keyword',
                    ],
                ]
            ],
            'name' => [
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
        ]
    ];

    public function toSearchableArray() {
        $array = $this->toArray();

        $array['alignment'] = $this->attributes['alignment'];

        return $array;
    }

    public function preference() {
        return $this->hasOne(UnitModPreference::class, 'unit_id', 'base_id');
    }
    public function getNameAttribute($value) {
        return __('messages.'.$value);
    }
    public function getShortNameAttribute($value) {
        $name = $this->name;

        $name = str_replace('General', 'Gen', $name);
        $name = str_replace('Geonosian', 'Geo', $name);
        $name = str_replace('Jedi Knight ', '', $name);
        $name = preg_replace('/\(.+\)/', '', $name);

        return trim($name);
    }
    public function getDescriptionAttribute($value) {
        return __('messages.'.$value);
    }
    public function getAlignmentAttribute($value) {
        return strtolower((new Alignment($value))->getKey());
    }
}
