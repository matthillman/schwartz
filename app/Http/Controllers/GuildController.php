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
            'guilds' => Guild::orderBy('schwartz', 'desc')->orderBy('gp', 'desc')->get(),
            'squads' => $this->squadList(),
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

        $units = Unit::all();
        $teams = collect($teams)->mapWithKeys(function($team, $title) use ($units) {
            return [$title => collect($team)->map(function($unit) use ($units) {
                return $units->first(function($u) use ($unit) { return $u->base_id === $unit; });
            })];
        });

        $view = $mode === 'members' ? 'member-teams' : 'guild-teams';
        return view("guild.$view", [
            'members' => $guild->members()->with('characters.zetas')->orderBy('player')->get(),
            'teams' => $teams,
            'highlight' => $highlight,
            'team' => $team,
            'guild' => $guild,
        ]);
    }

    public function schwartzGuilds() {
        $guilds = Guild::where('schwartz', 1)->orderBy('gp', 'desc')->get();
        return view('gp', [
            'guilds' => $guilds,
            'members' => $guilds->first()->members,
        ]);
    }

    public function schwartzGuildMods() {
        $guilds = Guild::where('schwartz', 1)->orderBy('gp', 'desc')->get();
        return view('member-mods', [
            'guilds' => $guilds,
            'mods' => $guilds->first()->mod_data,
        ]);
    }

    public function guildGP($id) {
        $guild = Guild::where('id', $id)->get();
        return view('gp', [
            'guilds' => $guild,
            'members' => $guild->first()->members,
        ]);
    }

    public function guildMods($id) {
        $guild = Guild::where('id', $id)->get();
        return view('member-mods', [
            'guilds' => $guild,
            'mods' => $guild->first()->mod_data,
        ]);
    }

    public function listGP($guild = null) {
        $guild = is_null($guild) ? Guild::where('schwartz', '1') : Guild::findOrFail($guild);

        return response()->json($guild->members);
    }

    public function listMods($guild) {
        $guild = Guild::findOrFail($guild);

        return response()->json($guild->mod_data);
    }
}
