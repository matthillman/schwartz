<?php

namespace App;

use Laravel\Scout\Searchable;
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
        'abilities' => 'collection',
    ];

    public function toSearchableArray() {
        $array = $this->toArray();

        unset($array['skills']);
        unset($array['abilities']);
        unset($array['url']);
        unset($array['image']);
        unset($array['power']);
        unset($array['created_at']);
        unset($array['updated_at']);
        unset($array['relic_image']);
        $array['crew_list'] = collect($array['crew_list'])->pluck('unitId')->join(' ');
        $array['category_list'] = collect($array['category_list'])->join(' ');

        return $array;
    }

    public function preference() {
        return $this->hasOne(UnitModPreference::class, 'unit_id', 'base_id');
    }
    public function getNameAttribute($value) {
        return strlen($value) ? __('messages.'.$value) : $value;
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

    public function getIsShipAttribute() {
        return $this->combat_type !== 1;
    }
    public function getIsCharAttribute() {
        return $this->combat_type == 1;
    }
    public function getHasUltimateAttribute() {
        return $this->abilities->where('powerAdditiveTag', 'ultimate')->isNotEmpty();
    }
}
