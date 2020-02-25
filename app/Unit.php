<?php

namespace App;

use ScoutElastic\Searchable;
use SwgohHelp\Enums\Alignment;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use Searchable;

    protected $fillable = ['base_id'];

    protected $casts = [
        'crew_list' => 'array',
        'category_list' => 'array',
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

    public function preference() {
        return $this->hasOne(UnitModPreference::class, 'unit_id', 'base_id');
    }
    public function getNameAttribute($value) {
        return __('messages.'.$value);
    }
    public function getDescriptionAttribute($value) {
        return __('messages.'.$value);
    }
    public function getAlignmentAttribute($value) {
        return strtolower((new Alignment($value))->getKey());
    }
}
