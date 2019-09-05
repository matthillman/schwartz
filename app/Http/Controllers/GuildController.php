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
                    'CLS Scoundrels' => ['COMMANDERLUKESKYWALKER', 'HANSOLO', 'CHEWBACCALEGENDARY', 'YOUNGCHEWBACCA', 'L3_37'],
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

    public function schwartzGuildMods() {
        return view('member-mods', [
            'guilds' => Guild::where('schwartz', 1)->orderBy('gp', 'desc')->get()
        ]);
    }

    public function listGP($guild = null) {
        $guild = is_null($guild) ? Guild::where('schwartz', '1') : Guild::findOrFail($guild);

        return response()->json($guild->members);
    }

    public function listMods($guild) {
        $guild = Guild::findOrFail($guild);
        $mods = \DB::table('guilds')
        ->join('members', 'members.guild_id', '=', 'guilds.id')
        ->join('mod_users', 'mod_users.name', '=', 'members.ally_code')
        ->join('mod_stats', 'mod_stats.mod_user_id', '=', 'mod_users.id')
        ->selectRaw("
            members.id,
            members.player,
            members.url,
            members.ally_code,
            sum(case when pips = 6 then 1 else 0 end) as six_dot,
            sum(case when speed >= 10 then 1 else 0 end) as speed_10,
            sum(case when speed >= 15 then 1 else 0 end) as speed_15,
            sum(case when speed >= 20 then 1 else 0 end) as speed_20,
            sum(case when speed >= 25 then 1 else 0 end) as speed_25,
            sum(case when offense >= 100 then 1 else 0 end) as offense_100
        ")
        ->groupBy('members.id', 'members.player', 'members.url', 'members.ally_code')
        ->whereIn('guilds.guild_id', [$guild->guild_id])
        ->get();

        return response()->json($mods);
    }
}
