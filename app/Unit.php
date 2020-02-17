<?php

namespace App;

use ScoutElastic\Searchable;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use Searchable;

    protected $fillable = ['base_id'];

    protected $casts = [
        'crew_list' => 'array',
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

        $array['description'] = __('messages.'.$array['description']);

        return $array;
    }

    public function preference() {
        return $this->hasOne(UnitModPreference::class, 'unit_id', 'base_id');
    }
    public function getNameAttribute($value) {
        return __('messages.'.$value);
    }
}
