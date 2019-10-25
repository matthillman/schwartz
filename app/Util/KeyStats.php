<?php

namespace App\Util;

use SwgohHelp\Enums\UnitStat;

trait KeyStats {

    protected function getKeyStats() {
        static $_stats;
        if (!isset($_stats)) {
            $_stats = collect([
                "GRIEVOUS" => UnitStat::UNITSTATMAXHEALTH(),
                "DAKA" => UnitStat::UNITSTATMAXHEALTH(),
                "SITHMARAUDER" => UnitStat::UNITSTATATTACKDAMAGE(),
                "ANAKINKNIGHT" => UnitStat::UNITSTATATTACKDAMAGE(),
                "HANSOLO" => UnitStat::UNITSTATATTACKDAMAGE(),
                "ENFYSNEST" => UnitStat::UNITSTATRESISTANCE(),
                "BOSSK" => UnitStat::UNITSTATRESISTANCE(),
                "C3POLEGENDARY" => UnitStat::UNITSTATACCURACY(),
            ]);
        }

        return $_stats;
    }

    public function isPercentStat(UnitStat $stat) {
        return in_array($stat->getKey(), ['UNITSTATRESISTANCE', 'UNITSTATACCURACY'], true);
    }

    public function keyStatsFor($unit) {
        return $this->getKeyStats()->filter(function ($stat, $key) use ($unit) {
            return $key === $unit;
        })
        ->prepend(UnitStat::UNITSTATSPEED())
        ->mapWithKeys(function ($stat) {
            $val = array_get($this->stats, 'final'.$stat->getValue(), 0);
            return [$stat->displayString() => $this->isPercentStat($stat) ? ($val * 100) . "%" : $val ];
        });
    }

}
