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
                    UnitStat::UNITSTATSPEED()->getKey() => [
                        'values' => [260, 265, 269],
                        'related' => [
                            'PADMEAMIDALA'  => ['=',  -21],
                        ]
                    ],
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

                'JEDIKNIGHTREVAN' => [
                    UnitStat::UNITSTATSPEED()->getKey() => [
                        'values' => [291, 296, 302],
                        'related' => [
                            'BASTILASHAN' => ['>=', [['+', 40], ['÷', 0.95], ['-', 40]]],
                            'HERMITYODA'  => ['<',  0],
                        ]
                    ],
                ],
                'ANAKINKNIGHT' => [
                    UnitStat::UNITSTATSPEED()->getKey() => [
                        'values' => [255, 260, 265],
                        'related' => [
                            'BASTILASHAN' => ['<=', [['+', 40], ['÷', 0.95], ['*', 0.89], ['-', 40]]],
                        ]
                    ],
                ],
                'BASTILASHAN' => [
                    UnitStat::UNITSTATSPEED()->getKey() => [275, 280, 285],
                ],
                'HERMITYODA' => [
                    UnitStat::UNITSTATSPEED()->getKey() => [
                        'values' => [292, 297, 303],
                        'related' => [
                            'BASTILASHAN' => ['>', [['+', 40], ['÷', 0.95], ['-', 40]]],
                        ]
                    ],
                ],
                'JOLEEBINDO' => [
                    UnitStat::UNITSTATRESISTANCE()->getKey() => [1, 1.25, 1.5],
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

    private function adjustStat($base, $operator, $value) {
        switch ($operator) {
            case '+': return $base + $value;
            case '-': return $base - $value;
            case '*': return $base * $value;
            case '÷': return $base / $value;

            default: return false;
        }
    }

    private function statCompare($left, $operator, $right) {
        $operator = trim($operator);
        switch ($operator) {
            case '>': return $left > $right;
            case '<': return $left < $right;
            case '=': return $left == $right;
            case '>=': return $left >= $right;
            case '<=': return $left <= $right;

            default: return false;
        }
    }

    private function isStatPercent($key) {
        static $pct = [
            false,//"None",
            false,//"Health",
            false,//"Strength",
            false,//"Agility",
            false,//"Intelligence",
            false,//"Speed",
            false,//"Physical Damage",
            false,//"Special Damage", //"UNIT_STAT_ABILITY_POWER",
            true,//"Armor", //"UNIT_STAT_ARMOR",
            true,//"Resistance", //"UNIT_STAT_SUPPRESSION",
            false,//"Armor Penetration", //"UNIT_STAT_ARMOR_PENETRATION",
            false,//"Resistance Penetration", //"UNIT_STAT_SUPPRESSION_PENETRATION",
            false,//"Dodge Rating", //"UNIT_STAT_DODGE_RATING",
            false,//"Deflection Rating", //"UNIT_STAT_DEFLECTION_RATING",
            true,//"Physical Critical Rating", //"UNIT_STAT_ATTACK_CRITICAL_RATING",
            true,//"Special Critical Rating", //"UNIT_STAT_ABILITY_CRITICAL_RATING",
            true,//"Critical Damage", 16
            true,//"Potency", 17
            true,//"Tenacity", 18
            true,//"Dodge Chance", //"UNIT_STAT_DODGE_PERCENT_ADDITIVE",
            true,//"Deflection Chance", //"UNIT_STAT_DEFLECTION_PERCENT_ADDITIVE",
            true,//"Physical Critical Chance", //"UNIT_STAT_ATTACK_CRITICAL_PERCENT_ADDITIVE",
            true,//"Special Critical Chance", //"UNIT_STAT_ABILITY_CRITICAL_PERCENT_ADDITIVE",
            true,//"UNIT_STAT_ARMOR_PERCENT_ADDITIVE",
            true,//"UNIT_STAT_SUPPRESSION_PERCENT_ADDITIVE",
            true,//"UNIT_STAT_ARMOR_PENETRATION_PERCENT_ADDITIVE",
            true,//"UNIT_STAT_SUPPRESSION_PENETRATION_PERCENT_ADDITIVE",
            true,//"Health Steal",//"UNIT_STAT_HEALTH_STEAL", 27
            false,//"Protection", 28
            true,//"UNIT_STAT_SHIELD_PENETRATION",
            true,//"UNIT_STAT_HEALTH_REGEN",
            true,//"UNIT_STAT_ATTACK_DAMAGE_PERCENT_ADDITIVE",
            true,//"UNIT_STAT_ABILITY_POWER_PERCENT_ADDITIVE",
            true,//"UNIT_STAT_DODGE_NEGATE_PERCENT_ADDITIVE",
            true,//"UNIT_STAT_DEFLECTION_NEGATE_PERCENT_ADDITIVE",
            true,//"UNIT_STAT_ATTACK_CRITICAL_NEGATE_PERCENT_ADDITIVE",
            true,//"UNIT_STAT_ABILITY_CRITICAL_NEGATE_PERCENT_ADDITIVE",
            true,//"UNIT_STAT_DODGE_NEGATE_RATING",
            true,//"UNIT_STAT_DEFLECTION_NEGATE_RATING",
            true,//"UNIT_STAT_ATTACK_CRITICAL_NEGATE_RATING",
            true,//"UNIT_STAT_ABILITY_CRITICAL_NEGATE_RATING",
            false,//"Offense",
            false,//"Defense",
            true,//"UNIT_STAT_DEFENSE_PENETRATION",
            true,//"UNIT_STAT_EVASION_RATING",
            true,//"UNIT_STAT_CRITICAL_RATING",
            true,//"UNIT_STAT_EVASION_NEGATE_RATING",
            true,//"UNIT_STAT_CRITICAL_NEGATE_RATING",
            true,//"Offense %", 48
            true,//"Defense %", 49
            true,//"UNIT_STAT_DEFENSE_PENETRATION_PERCENT_ADDITIVE",
            true,//"UNIT_STAT_EVASION_PERCENT_ADDITIVE",
            true,//"Accuracy", 52
            true,//"Critical Chance", 53
            true,//"Critical Avoidance", 54
            true,//"Health %", 55
            true,//"Protection %", 56
            true,//"Speed %",// "UNIT_STAT_SPEED_PERCENT_ADDITIVE",
            true,//"UNIT_STAT_COUNTER_ATTACK_RATING",
            true,//"UNIT_STAT_TAUNT" 59
            true,//"UNITSTATDEFENSEPENETRATIONTARGETPERCENTADDITIVE"
            false,//"UNITSTATMASTERY" 61
        ];

        return $pct[+$key];
    }

}
