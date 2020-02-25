<?php

namespace App\Util;

use DB;
use App\Member;
use App\ModUser;
use SwgohHelp\Enums\UnitStat;

trait ScoresMods {

    public $user;
    public $member;
    public $unit;

    private $secondaryList = [
        'speed',
        'critical_chance',
        'potency',
        'tenacity',
        'offense',
        'defense',
        'health',
        'protection',
        'offense_percent',
        'defense_percent',
        'health_percent',
        'protection_percent',
    ];
    private $setCounts = [
        'offense' => 4,
        'speed' => 4,
        'critdamage' => 4,
        'health' => 2,
        'defense' => 2,
        'critchance' => 2,
        'tenacity' => 2,
        'potency' => 2,
    ];
    private $slots = ['square', 'diamond', 'triangle', 'circle', 'cross', 'arrow'];

    // The query object
    private $modQuery;
    // The fetched results of the query
    private $allMods;
    // The fetched results grouped by set -> slot -> mods
    private $modLookup;

    private $rank;

    public function setUp($allyCode) {
        $this->modUser = ModUser::with('stats')->where(['name' => $allyCode])->first();
        $this->member = $this->modUser->member;

        $this->modQuery = $user->stats()->select('mod_stats.*');
        foreach ($secondaryList as $secondary) {
            $this->modQuery->leftJoin("mod_stat_${secondary}", "mod_stat_${secondary}.id", '=', 'mod_stats.id');
            $this->modQuery->selectRaw("mod_stat_${secondary}.percentile as ${secondary}_percentile");
        }
    }

    public function doFetch() {
        $this->allMods = $modQuery->get();

        // Do some pre-sorting

        $this->modLookup = $allMods
            ->groupBy('set')
            ->toBase()
            ->mapWithKeys(function($mods, $set) {
                return [$set => collect($mods)->groupBy('slot')];
            })
        ;
    }

    public function suggestModsFor($unitName, $ranking) {
        $this->unit = $this->member->characters()->where('unit_name', $unitName)->first();
        $this->rank = $ranking;

        $this->rank['primaryTotal'] = [];
        foreach($this->slots as $slot) {
            $this->rank['primaryTotal'][$slot] = array_reduce(array_values($this->rank[$slot]), 'max', 0);
        }
        $this->rank['secondaryTotal'][$slot] = array_reduce(array_values($this->rank['secondary']), 'max', 0);

        if (isset($this->rank['target'])) {
            return $this->suggestForTarget();
        }

        return $this->suggestByScore();
    }

    function suggestByScore() {

    }

    function suggestForTarget() {
        
    }

}
