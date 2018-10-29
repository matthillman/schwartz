<?php

namespace App\Http\Controllers;

use Artisan;
use App\Unit;
use App\Guild;
use Illuminate\Http\Request;
use App\Jobs\ProcessGuild;

class GuildController extends Controller
{
    public function listGuilds() {
        return view('guilds', [
            'guilds' => Guild::orderBy('schwartz', 'desc')->orderBy('gp', 'desc')->get()
        ]);
    }

    public function addGuild(Request $request) {
        $validated = $request->validate([
            'guild' => 'required|integer'
        ]);
        $guild = Guild::firstOrNew(['guild_id' => $validated['guild']]);

        if (is_null($guild->id)) {
            $guild->name = 'GUILD ' . $guild->guild_id;
            $guild->url = 'not_scraped';
            $guild->gp = 0;
            $guild->save();
        }

        ProcessGuild::dispatch($guild);

        return redirect()->route('guilds')->with('guildStatus', "Guild added");
    }

    public function scrapeGuild($guild) {
        $guild = Guild::findOrFail($guild);

        ProcessGuild::dispatch($guild);

        return redirect()->route('guilds')->with('guildStatus', "Guild scrape queued");
    }

    public function listMembers($guild, $team, $mode = 'guild') {
        $guild = Guild::findOrFail($guild);

        $highlight = "gear";
        switch (strtolower($team)) {
            case 'str':
                $teams = [
                    'RJT' => ['REYJEDITRAINING', 'BB8', 'R2D2_LEGENDARY', 'REY', 'RESISTANCETROOPER', 'VISASMARR', 'HERMITYODA'],
                    'Chex' => ['COMMANDERLUKESKYWALKER', 'HANSOLO', 'DEATHTROOPER', 'CHIRRUTIMWE', 'PAO', 'CT7567', 'ANAKINKNIGHT'],
                    'Nightsisters' => ['ASAJVENTRESS', 'DAKA', 'NIGHTSISTERZOMBIE', 'MOTHERTALZIN', 'TALIA', 'NIGHTSISTERACOLYTE', 'NIGHTSISTERINITIATE'],
                ];
                break;
            case 'rjt':
                $teams = [
                    'RJT' => ['REYJEDITRAINING', 'REY', 'BB8', 'FINN', 'SMUGGLERHAN', 'SMUGGLERCHEWBACCA'],
                ];
                $highlight = 'stars';
                break;
            case 'bh':
                $teams = [
                    'Bounty Hunters' => ['BOSSK', 'BOBAFETT', 'GREEDO', 'DENGAR', 'ZAMWESELL', 'CADBANE', 'IG88', 'EMBO', 'JANGOFETT'],
                ];
                $highlight = 'stars';
                break;
            case 'newie':
                $teams = [
                    'Newie' => ['CHEWBACCALEGENDARY'],
                ];
                $highlight = 'stars';
                break;
            case 'kotor':
                $teams = [
                    'Revan' => ['JEDIKNIGHTREVAN', 'BASTILASHAN', 'ZAALBAR', 'MISSIONVAO', 'JOLEEBINDO', 'T3_M4'],
                ];
                $highlight = 'stars';
                break;
            case 'tw':
                $teams = [
                    'Traya' => ['DARTHTRAYA', 'DARTHSION', 'DARTHNIHILUS', 'SITHTROOPER', 'VISASMARR', 'GRANDADMIRALTHRAWN', 'ENFYSNEST', 'WAMPA'],
                    'KRU' => ['KYLORENUNMASKED', 'KYLOREN', 'FIRSTORDEROFFICERMALE', 'FIRSTORDEREXECUTIONER', 'FIRSTORDERTROOPER'],
                    'Bounty Hunters' => ['BOSSK', 'BOBAFETT', 'GREEDO', 'DENGAR', 'EMBO', 'BARRISSOFFEE'],
                    'Revan' => ['JEDIKNIGHTREVAN', 'JOLEEBINDO', 'BASTILASHAN', 'GRANDMASTERYODA', 'GENERALKENOBI'],
                    'Jedi' => ['BASTILASHAN', 'GRANDMASTERYODA', 'GENERALKENOBI', 'FULCRUMAHSOKA', 'EZRABRIDGERS3'],
                    'Smugglers' => ['QIRA', 'ZAALBAR', 'YOUNGCHEWBACCA', 'L3_37', 'ENFYSNEST'],
                    'Palp' => ['EMPERORPALPATINE', 'VADER', 'GRANDMOFFTARKIN', 'TIEFIGHTERPILOT', 'ROYALGUARD'],
                ];
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

            default:
                $teams = [];
                break;
        }

        $view = $mode === 'members' ? 'member-teams' : 'guild-teams';
        return view("guild.$view", [
            'members' => $guild->members()->with('characters.zetas')->orderBy('player')->get(),
            'units' => Unit::all(),
            'teams' => $teams,
            'highlight' => $highlight,
            'team' => $team,
            'guild' => $guild,
        ]);
    }

    public function schwartzGuilds() {
        return view('gp', [
            'guilds' => Guild::where('schwartz', 1)->orderBy('gp', 'desc')->get()
        ]);
    }

    public function listGP($guild = null) {
        $guild = is_null($guild) ? Guild::where('schwartz', '1') : Guild::findOrFail($guild);

        return response()->json($guild->members);
    }
}
