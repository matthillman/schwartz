<?php

namespace App\Http\Controllers\Util;

trait Squads {
    protected function getSquadsFor($key) {
        $highlight = "gear";
        switch (strtolower($key)) {
            case 'str':
                $teams = [
                    'RJT' => ['REYJEDITRAINING', 'BB8', 'R2D2_LEGENDARY', 'REY', 'RESISTANCETROOPER', 'VISASMARR', 'HERMITYODA'],
                    'Chex' => ['COMMANDERLUKESKYWALKER', 'HANSOLO', 'DEATHTROOPER', 'CHIRRUTIMWE', 'PAO', 'CT7567', 'ANAKINKNIGHT'],
                    'Nightsisters' => ['ASAJVENTRESS', 'DAKA', 'NIGHTSISTERZOMBIE', 'MOTHERTALZIN', 'TALIA', 'NIGHTSISTERACOLYTE', 'NIGHTSISTERINITIATE'],
                ];
                break;
            case 'legendary':
                $teams = [
                    'Revan' => ['JEDIKNIGHTREVAN', 'BASTILASHAN', 'ZAALBAR', 'MISSIONVAO', 'JOLEEBINDO', 'T3_M4'],
                    'Darth Revan' => ['DARTHREVAN', 'CARTHONASI', 'BASTILASHANDARK', 'HK47', 'JUHANI', 'CANDEROUSORDO'],
                    'Darth Malak' => ['DARTHMALAK'],
                    'C3PO' => ['C3POLEGENDARY', 'CHIEFCHIRPA', 'PAPLOO', 'EWOKELDER', 'LOGRAY', 'WICKET', 'EWOKSCOUT', 'TEEBO'],
                    'RJT' => ['REYJEDITRAINING', 'REY', 'BB8', 'FINN', 'SMUGGLERHAN', 'SMUGGLERCHEWBACCA'],
                    'Newie' => ['CHEWBACCALEGENDARY', 'BOSSK', 'BOBAFETT', 'GREEDO', 'DENGAR', 'ZAMWESELL', 'CADBANE', 'IG88', 'EMBO', 'JANGOFETT'],
                    'Padmé Amidala' => ['PADMEAMIDALA', 'GRIEVOUS', 'B2SUPERBATTLEDROID', 'MAGNAGUARD', 'B1BATTLEDROIDV2', 'DROIDEKA', 'COUNTDOOKU', 'NUTEGUNRAY', 'ASAJVENTRESS', 'WATTAMBOR'],
                    'OG MF' => ['MILLENNIUMFALCON', 'HOUNDSTOOTH', 'IG2000', 'XANADUBLOOD', 'SLAVE1'],
                ];
                $highlight = 'stars';
                break;
            case 'malak':
                $teams = [
                    'Darth Malak' => ['DARTHMALAK'],
                    'Revan' => ['JEDIKNIGHTREVAN', 'BASTILASHAN', 'ZAALBAR', 'MISSIONVAO', 'JOLEEBINDO', 'T3_M4'],
                    'Darth Revan' => ['DARTHREVAN', 'CARTHONASI', 'BASTILASHANDARK', 'HK47', 'JUHANI', 'CANDEROUSORDO'],
                ];
                $highlight = 'power';
                break;
            case 'tw':
                $teams = [
                    'Darth Revan' => ['DARTHREVAN', 'BASTILASHANDARK', 'DARTHMALAK', 'HK47', 'SITHMARAUDER', 'SITHTROOPER'],
                    'GG' => ['GRIEVOUS', 'B2SUPERBATTLEDROID', 'MAGNAGUARD', 'B1BATTLEDROIDV2', 'DROIDEKA', 'NUTEGUNRAY'],
                    'Nightsisters' => ['MOTHERTALZIN', 'ASAJVENTRESS', 'DAKA', 'NIGHTSISTERZOMBIE', 'NIGHTSISTERSPIRIT'],
                    'CLS Scoundrels' => ['COMMANDERLUKESKYWALKER', 'HANSOLO', 'CHEWBACCALEGENDARY', 'ENFYSNEST', 'L3_37'],
                    'Bounty Hunters' => ['JANGOFETT', 'BOSSK', 'BOBAFETT', 'ZAMWESELL', 'DENGAR'],
                    'Geonosians' => ['GEONOSIANBROODALPHA', 'GEONOSIANSOLDIER', 'GEONOSIANSPY', 'POGGLETHELESSER', 'SUNFAC'],
                    'Padmé' => ['PADMEAMIDALA', 'ANAKINKNIGHT', 'AHSOKATANO', 'GENERALKENOBI', 'C3POLEGENDARY'],
                    'Clones' => ['SHAAKTI', 'CT7567', 'CT5555', 'CT210408', 'CC2224', 'CLONESERGEANTPHASEI'],
                ];
                break;
            case 'geo':
                $teams = [
                    'Seperatists' => ['COUNTDOOKU', 'NUTEGUNRAY', 'ASAJVENTRESS', 'WATTAMBOR'],
                    'Droids' => ['GRIEVOUS', 'B2SUPERBATTLEDROID', 'MAGNAGUARD', 'B1BATTLEDROIDV2', 'DROIDEKA'],
                    'Geonosians' => ['GEONOSIANBROODALPHA', 'GEONOSIANSOLDIER', 'GEONOSIANSPY', 'POGGLETHELESSER', 'SUNFAC'],
                    'Darth Revan' => ['DARTHREVAN', 'BASTILASHANDARK', 'DARTHMALAK', 'HK47', 'SITHMARAUDER'],
                    'Nightsisters' => ['MOTHERTALZIN', 'ASAJVENTRESS', 'DAKA', 'NIGHTSISTERZOMBIE', 'NIGHTSISTERSPIRIT'],
                    'Traya' => ['DARTHTRAYA', 'DARTHNIHILUS', 'DARTHSION', 'SITHTROOPER'],
                ];
                $highlight = 'power';
                break;
            case 'tb':
                $teams = [
                    'Phoenix' => ['HERASYNDULLAS3', 'EZRABRIDGERS3', 'SABINEWRENS3', 'CHOPPERS3', 'KANANJARRUSS3', 'ZEBS3'],
                    'Rogue One' => ['JYNERSO', 'K2SO', 'CASSIANANDOR', 'CHIRRUTIMWE', 'BAZEMALBUS', 'SCARIFREBEL', 'BISTAN'],
                    'Bounty Hunters' => ['BOSSK', 'BOBAFETT', 'GREEDO', 'DENGAR', 'ZAMWESELL', 'CADBANE', 'IG88', 'EMBO', 'JANGOFETT'],
                    'Troopers' => ['VEERS', 'COLONELSTARCK', 'IMPERIALPROBEDROID', 'SNOWTROOPER', 'STORMTROOPER', 'DEATHTROOPER', 'RANGETROOPER', 'SHORETROOPER', 'MAGMATROOPER'],
                    'Hoth People' => ['COMMANDERLUKESKYWALKER', 'HOTHLEIA', 'HOTHHAN', 'HOTHREBELSCOUT', 'HOTHREBELSOLDIER'],
                ];
                $highlight = 'stars';
                break;
            case 'gs':
                $teams = [
                    'Tier 1' => ['CAPITALNEGOTIATOR', 'CAPITALJEDICRUISER', 'JEDISTARFIGHTERANAKIN', 'UMBARANSTARFIGHTER', 'JEDISTARFIGHTERAHSOKATANO', 'ARC170CLONESERGEANT', 'ARC170REX', 'BLADEOFDORIN', 'JEDISTARFIGHTERCONSULAR'],
                    'Tier 2' => ['AHSOKATANO', 'C3POLEGENDARY', 'GENERALKENOBI', 'PADMEAMIDALA', 'SHAAKTI'],
                    'Tier 4' => ['ASAJVENTRESS', 'B1BATTLEDROIDV2', 'B2SUPERBATTLEDROID', 'DROIDEKA', 'MAGNAGUARD'],
                ];
                $highlight = 'power-stars';
                break;
            default:
                $teams = [];
                break;
        }

        return [$highlight, $teams];
    }
}