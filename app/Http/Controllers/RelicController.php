<?php

namespace App\Http\Controllers;

use App\Member;
use App\Character;
use Illuminate\Http\Request;

class RelicController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $relics = collect($this->getRecommendations())->mapWithKeys(function($chars, $level) {
            return [$level => collect($chars)->sortBy('priority')->values()->map(function($char) use ($level) {
                return ['unit' => $this->charFor($char, $level), 'priority' => $char['priority']];
            })];
        });
        return view('relics', [
            'relics' => $relics,
        ]);
    }

    public function relicMember($allyCode)
    {
        $member = Member::where('ally_code', $allyCode)->firstOrFail();
        $relics = collect($this->getRecommendations())->mapWithKeys(function($chars, $level) use ($member) {
            return [$level => collect($chars)->sortBy('priority')->values()->map(function($char) use ($member) {
                return ['unit' => $this->charFor($char, 0, $member), 'priority' => $char['priority']];
            })];
        });
        return view('relics', [
            'relics' => $relics,
            'member' => $member,
        ]);
    }

    private function charFor($unit, $level, $member = null) {
        $memChar = null;
        if (!is_null($member)) {
            $memChar = $member->characters()->where('unit_name', $unit['unit'])->first();
        }
        if (is_null($memChar)) {
            $memChar = new Character([
                'unit_name' => $unit['unit'],
                'gear_level' => is_null($member) ? 13 : 1,
                'power' => 0,
                'level' => is_null($member) ? 85 : 1,
                'combat_type' => 1,
                'rarity' => is_null($member) ? 7 : 0,
                'relic' => is_null($member) ? $level + 2 : 0,
            ]);
        }
        return $memChar;
    }

    private function getRecommendations() {
        return [
            '7' => [
                ['unit' => 'ANAKINKNIGHT', 'priority' => 1],
                ['unit' => 'GENERALKENOBI', 'priority' => 1],
                ['unit' => 'GENERALSKYWALKER', 'priority' => 1],
                ['unit' => 'CT210408', 'priority' => 1],
                ['unit' => 'CT5555', 'priority' => 1],
                ['unit' => 'ARCTROOPER501ST', 'priority' => 1],
                ['unit' => 'HANSOLO', 'priority' => 1],
            ],
            '5' => [
                ['unit' => 'DARTHMALAK', 'priority' => 1],
                ['unit' => 'GRIEVOUS', 'priority' => 1],
                ['unit' => 'B1BATTLEDROIDV2', 'priority' => 1],
                ['unit' => 'CHEWBACCALEGENDARY', 'priority' => 1],
                ['unit' => 'HK47', 'priority' => 2],
                ['unit' => 'SITHMARAUDER', 'priority' => 1],
                ['unit' => 'JOLEEBINDO', 'priority' => 1],
                ['unit' => 'AHSOKATANO', 'priority' => 1],
                ['unit' => 'BOSSK', 'priority' => 1],
                ['unit' => 'COMMANDERLUKESKYWALKER', 'priority' => 1],
            ],
            '3' => [
                ['unit' => 'DARTHSION', 'priority' => 3],
                ['unit' => 'DARTHTRAYA', 'priority' => 3],
                ['unit' => 'B2SUPERBATTLEDROID', 'priority' => 3],
                ['unit' => 'GRANDADMIRALTHRAWN', 'priority' => 3],
                ['unit' => 'ADMIRALACKBAR', 'priority' => 1],
                ['unit' => 'R2D2_LEGENDARY', 'priority' => 2],
                ['unit' => 'BISTAN', 'priority' => 1],
                ['unit' => 'SCARIFREBEL', 'priority' => 1],
                ['unit' => 'BOBAFETT', 'priority' => 2],
                ['unit' => 'JANGOFETT', 'priority' => 2],
                ['unit' => 'C3POLEGENDARY', 'priority' => 3],
                ['unit' => 'CT7567', 'priority' => 1],
                ['unit' => 'BARRISSOFFEE', 'priority' => 2],
                ['unit' => 'SHAAKTI', 'priority' => 2],
                ['unit' => 'PADMEAMIDALA', 'priority' => 3],
                ['unit' => 'GEONOSIANBROODALPHA', 'priority' => 2],
                ['unit' => 'GEONOSIANSPY', 'priority' => 2],
                ['unit' => 'JEDIKNIGHTREVAN', 'priority' => 3],
                ['unit' => 'BASTILASHAN', 'priority' => 3],
                ['unit' => 'HERMITYODA', 'priority' => 3],
                ['unit' => 'OLDBENKENOBI', 'priority' => 3],
                ['unit' => 'TIEFIGHTERPILOT', 'priority' => 5],
                ['unit' => 'BIGGSDARKLIGHTER', 'priority' => 5],
            ],
        ];
    }
}
