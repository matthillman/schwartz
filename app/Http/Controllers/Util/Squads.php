<?php

namespace App\Http\Controllers\Util;

use App\SquadGroup;

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
                    'General Skywalker' => ['GENERALSKYWALKER', 'CT7567', 'CT5555', 'CT210408', 'ARCTROOPER501ST', 'AHSOKATANO', 'SHAAKTI', 'CLONESERGEANTPHASEI', 'BARRISSOFFEE'],
                    'GG' => ['GRIEVOUS', 'B2SUPERBATTLEDROID', 'MAGNAGUARD', 'B1BATTLEDROIDV2', 'DROIDEKA', 'NUTEGUNRAY', 'BB8', 'WATTAMBOR'],
                    'Nightsisters' => ['MOTHERTALZIN', 'ASAJVENTRESS', 'DAKA', 'NIGHTSISTERZOMBIE', 'NIGHTSISTERSPIRIT', 'NIGHTSISTERACOLYTE'],
                    'The Nute' => ['NUTEGUNRAY', 'COUNTDOOKU', 'ENFYSNEST', 'L3_37', 'ZAALBAR'],
                    'Darth Revan' => ['DARTHREVAN', 'BASTILASHANDARK', 'DARTHMALAK', 'HK47', 'SITHMARAUDER', 'SITHTROOPER'],
                    'Padmé' => ['PADMEAMIDALA', 'C3POLEGENDARY', 'GENERALKENOBI', 'AHSOKATANO', 'R2D2_LEGENDARY', 'SHAAKTI'],
                    'Revan' => ['JEDIKNIGHTREVAN', 'ANAKINKNIGHT', 'BASTILASHAN', 'HERMITYODA', 'JOLEEBINDO'],
                    'CLS' => ['COMMANDERLUKESKYWALKER', 'HANSOLO', 'CHEWBACCALEGENDARY', 'WATTAMBOR', 'OLDBENKENOBI', 'STORMTROOPERHAN'],
                    'Geonosians' => ['GEONOSIANBROODALPHA', 'GEONOSIANSOLDIER', 'GEONOSIANSPY', 'POGGLETHELESSER', 'SUNFAC'],
                    'Bounty Hunters' => ['JANGOFETT', 'BOSSK', 'BOBAFETT', 'ZAMWESELL', 'DENGAR', 'ENFYSNEST', 'EMBO'],
                ];
                break;
            case 'tw_mods':
                $teams = [
                    'Padmé' => ['PADMEAMIDALA', 'C3POLEGENDARY', 'GENERALKENOBI', 'AHSOKATANO', 'R2D2_LEGENDARY'],
                    'Revan' => ['JEDIKNIGHTREVAN', 'ANAKINKNIGHT', 'BASTILASHAN', 'HERMITYODA', 'JOLEEBINDO'],
                ];
                $highlight = 'mods';
                break;
            case 'geo':
                $teams = [
                    'Seperatists' => ['COUNTDOOKU', 'NUTEGUNRAY', 'ASAJVENTRESS', 'WATTAMBOR'],
                    'Droids' => ['GRIEVOUS', 'B2SUPERBATTLEDROID', 'MAGNAGUARD', 'B1BATTLEDROIDV2', 'DROIDEKA'],
                    'Geonosians' => ['GEONOSIANBROODALPHA', 'GEONOSIANSOLDIER', 'GEONOSIANSPY', 'POGGLETHELESSER', 'SUNFAC'],
                    'Darth Revan' => ['DARTHREVAN', 'BASTILASHANDARK', 'DARTHMALAK', 'HK47', 'SITHMARAUDER'],
                    'Nightsisters' => ['MOTHERTALZIN', 'ASAJVENTRESS', 'DAKA', 'NIGHTSISTERZOMBIE', 'NIGHTSISTERSPIRIT'],
                    'Traya' => ['DARTHTRAYA', 'DARTHNIHILUS', 'DARTHSION', 'SITHTROOPER'],
                    'P4 SM' => ['NUTEGUNRAY', 'JANGOFETT', 'DROIDEKA', 'SUNFAC', 'WATTAMBOR'],
                    'P4 Dooku' => ['COUNTDOOKU', 'GEONOSIANBROODALPHA', 'GEONOSIANSOLDIER', 'GEONOSIANSPY', 'POGGLETHELESSER'],
                    'Key Ships' => ['CAPITALMALEVOLENCE', 'HYENABOMBER', 'VULTUREDROID', 'YWINGCLONEWARS'],
                ];
                $highlight = 'power';
                break;
            case 'lsgeo':
                $teams = [
                    'Padmé' => ['PADMEAMIDALA', 'ANAKINKNIGHT', 'AHSOKATANO', 'GENERALKENOBI', 'C3POLEGENDARY'],
                    'General Skywalker' => ['GENERALSKYWALKER', 'CT7567', 'CT5555', 'CT210408', 'ARCTROOPER501ST'],
                    'Clones' => ['SHAAKTI', 'CC2224', 'CLONESERGEANTPHASEI'],
                    'Revan Jedi' => ['JEDIKNIGHTREVAN', 'BASTILASHAN', 'JOLEEBINDO', 'HERMITYODA'],
                    'Key GR Jedi' => ['KIADIMUNDI', 'GRANDMASTERYODA', 'BARRISSOFFEE'],
                    'GR Jedi' => ['MACEWINDU', 'PLOKOON', 'KITFISTO', 'LUMINARAUNDULI', 'QUIGONJINN', 'EETHKOTH', 'IMAGUNDI', 'JEDIKNIGHTCONSULAR'],
                    'Other Jedi' => ['EZRABRIDGERS3', 'JUHANI', 'KANANJARRUSS3', 'OLDBENKENOBI'],
                    'KAM SM' => ['SHAAKTI', 'CT7567', 'CT5555', 'CT210408', 'ARCTROOPER501ST'],
                    'Negotiator' => ['CAPITALNEGOTIATOR', 'JEDISTARFIGHTERANAKIN', 'UMBARANSTARFIGHTER', 'JEDISTARFIGHTERAHSOKATANO', 'YWINGCLONEWARS'],
                    'Mace' => ['CAPITALJEDICRUISER', 'ARC170CLONESERGEANT', 'ARC170REX', 'BLADEOFDORIN', 'JEDISTARFIGHTERCONSULAR'],
                    'Ackbar' => ['CAPITALMONCALAMARICRUISER', 'MILLENNIUMFALCON', 'XWINGRED3', 'UWINGSCARIF', 'UWINGROGUEONE', 'GHOST', 'PHANTOM2', 'XWINGRED2'],
                    'Other LS Ships' => ['MILLENNIUMFALCONPRISTINE', 'EBONHAWK', 'XWINGBLACKONE', 'XWINGRESISTANCE', 'MILLENNIUMFALCONEP7']
                ];
                $highlight = 'power-plus';
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
                    'Anakin' => ['GENERALSKYWALKER'],
                    'Tier 1' => ['CAPITALNEGOTIATOR', 'CAPITALJEDICRUISER', 'JEDISTARFIGHTERANAKIN', 'UMBARANSTARFIGHTER', 'JEDISTARFIGHTERAHSOKATANO', 'ARC170CLONESERGEANT', 'ARC170REX', 'BLADEOFDORIN', 'JEDISTARFIGHTERCONSULAR'],
                    'Tier 2' => ['AHSOKATANO', 'C3POLEGENDARY', 'GENERALKENOBI', 'PADMEAMIDALA', 'SHAAKTI'],
                    'Tier 4' => ['ASAJVENTRESS', 'B1BATTLEDROIDV2', 'B2SUPERBATTLEDROID', 'DROIDEKA', 'MAGNAGUARD'],
                ];
                $highlight = 'power-stars';
                break;
            case 'glkylo':
                $teams = [
                    'Challenge 1' => ['KYLORENUNMASKED', 'FIRSTORDERTROOPER', 'FIRSTORDEROFFICERMALE'],
                    'Challenge 2' => ['KYLOREN', 'PHASMA', 'FIRSTORDEREXECUTIONER'],
                    'Challenge 3' => ['SMUGGLERHAN', 'FOSITHTROOPER', 'FIRSTORDERSPECIALFORCESPILOT'],
                ];
                break;
            case 'glrey':
                $teams = [
                    'Challenge 1' => ['REYJEDITRAINING', 'FINN', 'RESISTANCETROOPER'],
                    'Challenge 2' => ['REY', 'RESISTANCEPILOT', 'POE'],
                    'Challenge 3' => ['EPIXFINN', 'AMILYNHOLDO', 'ROSETICO'],
                ];
                break;
            default:
                $teams = [];
                break;
        }

        return [$highlight, $teams];
    }

    public function squadLabelFor($key) {
        return collect($this->squadList(true))->first(function($pair) use ($key) {
            return $pair['value'] == $key;
        })['label'];
    }

    public function squadList($hideSeparators = false) {
        $globalSquads = SquadGroup::global()->get()
            ->map(function($group) {
                return ['label' => $group->name, 'value' => $group->id];
            });
        $defaultSquads = [
            ['label' => 'Galactic Legend: Kylo Ren', 'value' => 'glkylo'],
            ['label' => 'Galactic Legend: Rey', 'value' => 'glrey'],
            ['label' => 'General Skywalker', 'value' => 'gs'],
            ['label' => 'Legendaries', 'value' => 'legendary'],
            ['label' => 'Darth Malak', 'value' => 'malak'],
            ['label' => 'Hoth TB', 'value' => 'tb'],
            ['label' => 'STR', 'value' => 'str'],
            // ['label' => 'Geo TB', 'value' => 'geo'],
            ['label' => 'LS Geo TB', 'value' => 'lsgeo'],
            ['label' => 'TW', 'value' => 'tw'],
        ];

        $guildSquads = collect([]);
        foreach (auth()->user()->accounts as $account) {
            $s = SquadGroup::forGuild($account->guild)->get()
                ->map(function($group) use ($account) {
                    return ['label' => $group->name, 'value' => $group->id, 'guild' => $account->guild->id];
                });
            if (!$hideSeparators && $s->count() > 0) {
                $s->prepend(['label' => $account->guild->name, 'separator' => true, 'guild' => $account->guild->id]);
            }
            $guildSquads = $guildSquads->concat($s);
        }

        return $globalSquads->concat($defaultSquads)->concat($guildSquads);
    }
}