<?php

namespace App\Http\Controllers;

use Artisan;
use App\Unit;
use App\Guild;
use Illuminate\Http\Request;
use App\Jobs\ProcessGuild;

class GuildController extends Controller
{
    use Util\Squads;

    public function listGuilds() {
        return view('guilds', [
            'guilds' => Guild::orderBy('schwartz', 'desc')->orderBy('gp', 'desc')->get()
        ]);
    }

    public function addGuild(Request $request) {
        $validated = $request->validate([
            'guild' => 'required|integer'
        ]);

        ProcessGuild::dispatch($validated['guild']);

        return redirect()->route('guilds')->with('guildStatus', "Guild added");
    }

    public function scrapeGuild($guild) {
        $guild = Guild::findOrFail($guild);

        ProcessGuild::dispatch($guild->guild_id);

        return redirect()->route('guilds')->with('guildStatus', "Guild scrape queued");
    }

    public function listMembers($guild, $team, $mode = 'guild') {
        $guild = Guild::findOrFail($guild);

        list($highlight, $teams) = $this->getSquadsFor($team);

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

    public function guildGP($id) {
        return view('gp', [
            'guilds' => Guild::where('id', $id)->get()
        ]);
    }

    public function guildMods($id) {
        return view('member-mods', [
            'guilds' => Guild::where('id', $id)->get()
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
