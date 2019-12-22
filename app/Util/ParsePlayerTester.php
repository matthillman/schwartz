<?php

namespace App\Util;

use App\Member;

class ParsePlayerTester {
    use ParsesPlayers;

    private $characters;

    function test() {
        $me = Member::where('ally_code', '552325555')->first();
        $this->characters = $me->characters->makeHidden(['member', 'unit'])->toArray();

        $this->doShip($me->characters()->where('unit_name', 'YWINGCLONEWARS')->first(), 29271);
        $this->doShip($me->characters()->where('unit_name', 'JEDISTARFIGHTERANAKIN')->first(), 73340);
        $this->doShip($me->characters()->where('unit_name', 'UMBARANSTARFIGHTER')->first(), 74026);
        $this->doShip($me->characters()->where('unit_name', 'CAPITALNEGOTIATOR')->first(), 73222);
        $this->doShip($me->characters()->where('unit_name', 'MILLENNIUMFALCONPRISTINE')->first(), 53694);

        return "🍷";
    }

    function doShip($ship, $realGP) {
        $unit = $ship->toArray();
        $unit['raw'] = $ship->raw;

        $gp = $this->getShipGP($unit, $this->characters);
        echo "⚓️ ". $ship['unit_name'] . "\n🥯 Real GP is $realGP\n🍺 Calculated is $gp\n🥃 Difference: " . ($gp - $realGP);
        echo "\n\n";
    }
}