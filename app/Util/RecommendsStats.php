<?php

namespace App\Util;

use SwgohHelp\Enums\UnitStat;

trait RecommendsStats {

    protected function getStatRecommendations() {
        static $_stats;
        if (!isset($_stats)) {
            $_stats = collect([
                'PADMEAMIDALA' => [
                    UnitStat::UNITSTATSPEED()->getKey() => [270, 280, 290],
                    UnitStat::UNITSTATMAXHEALTH()->getKey() => [55000, 57500, 60000],
                ],
                'C3POLEGENDARY' => [
                    UnitStat::UNITSTATSPEED()->getKey() => [260, 265, 269],
                    UnitStat::UNITSTATMAXHEALTH()->getKey() => [50000, 52500, 55000],
                ],
                'GENERALKENOBI' => [
                    UnitStat::UNITSTATMAXHEALTH()->getKey() => [65000, 67500, 70000],
                    UnitStat::UNITSTATSPEED()->getKey() => [235, 240, 247],
                ],
                'AHSOKATANO' => [
                    UnitStat::UNITSTATMAXHEALTH()->getKey() => [45000, 47500, 50000],
                    UnitStat::UNITSTATSPEED()->getKey() => [205, 210, 215],
                ],
                'R2D2_LEGENDARY' => [
                    UnitStat::UNITSTATMAXHEALTH()->getKey() => [75000, 77500, 80000],
                    UnitStat::UNITSTATSPEED()->getKey() => [
                        'values' => [279, 284, 288],
                        'related' => [
                            'C3POLEGENDARY' => ['=', 19],
                            'PADMEAMIDALA'  => ['<',  0],
                        ]
                    ],
                ],
            ]);
        }

        return $_stats;
    }

    // public function isPercentStat(UnitStat $stat) {
    //     return in_array($stat->getKey(), ['UNITSTATRESISTANCE', 'UNITSTATACCURACY'], true);
    // }

    public function recommendationsFor($unit) {
        return collect($this->getStatRecommendations()->filter(function ($stat, $key) use ($unit) {
            return $key === $unit;
        })->get($unit));
    }

    private function statCompare($left, $operator, $right) {
        switch ($operator) {
            case '>': return $left > $right;
            case '<': return $left > $right;
            case '=': return $left === $right;
            case '>=': return $left >= $right;
            case '<=': return $left <= $right;

            default: return false;
        }
    }

}
