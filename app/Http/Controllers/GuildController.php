<?php

namespace App\Http\Controllers;

use Artisan;
use App\Unit;
use App\Guild;
use App\Character;
use App\Jobs\ProcessGuild;
use Illuminate\Http\Request;

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

    public function listMembers($guild, $team, $mode = 'guild', int $index = PHP_INT_MAX) {
        $guild = Guild::findOrFail($guild);

        list($highlight, $teams) = $this->getSquadsFor($team);

        $teamKeys = array_keys($teams);

        if (is_int($index) && $index < count($teamKeys)) {
            $teams = [
                $teamKeys[$index] => $teams[$teamKeys[$index]],
            ];
        }

        $units = Unit::all();
        $teams = collect($teams)->mapWithKeys(function($team, $title) use ($units) {
            return [$title => collect($team)->map(function($unit) use ($units) {
                return $units->first(function($u) use ($unit) { return $u->base_id === $unit; });
            })];
        });

        $view = $mode === 'members' ? 'member-teams' : 'guild-teams';
        return view("guild.$view", [
            'members' => $guild->members()
                ->select('id', 'player', 'gp', 'character_gp', 'ship_gp', 'ally_code', 'level')
                ->with(['characters' => function($query) use ($teams) {
                    $query->whereIn('unit_name', $teams->flatten()->pluck('base_id'));
                }])->orderBy('player')->get(),
            'teams' => $teams,
            'highlight' => $highlight,
            'team' => $team,
            'guild' => $guild,
            'teamKeys' => $teamKeys,
            'title' => $this->squadLabelFor($team),
            'selected' => $index,
        ]);
    }

    public function characterMods($characterID) {
        $character = Character::findOrFail($characterID);

        return response()->json($character->mods);
    }

    public function schwartzGuilds() {
        $guilds = Guild::where('schwartz', 1)->orderBy('gp', 'desc')->get();
        return view('gp', [
            'guilds' => $guilds,
            'members' => $guilds->first()->members,
        ]);
    }

    public function schwartzGuildsImportList() {
        $guilds = Guild::where('schwartz', 1)->orderBy('gp', 'desc')->get();
        return view('import-list', [
            'guilds' => $guilds,
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

    public function compareGuilds($first, $second) {
        if (preg_match('/^\d{3}-?\d{3}-?\d{3}$/', $first)) {
            $ally = preg_replace('/[^0-9]/', '', $first);
            $member = Member::where(['ally_code' => $ally])->firstOrFail();
            $guild1 = $member->guild()->firstOrFail();
        } else {
            $guild1 = \App\Guild::where(['guild_id' => $first])->firstOrFail();
        }
        if (preg_match('/^\d{3}-?\d{3}-?\d{3}$/', $second)) {
            $ally2 = preg_replace('/[^0-9]/', '', $second);
            $member2 = Member::where(['ally_code' => $ally2])->firstOrFail();
            $guild2 = $member2->guild()->firstOrFail();
        } else {
            $guild2 = \App\Guild::where(['guild_id' => $second])->firstOrFail();
        }

        dd(Guild::getCompareCharacters(), Guild::getCompareData($guild1, $guild2));
    }
}
