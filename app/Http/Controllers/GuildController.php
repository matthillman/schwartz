<?php

namespace App\Http\Controllers;

use Gate;
use Artisan;
use App\Unit;
use App\Guild;
use App\Member;
use App\Character;
use App\SquadGroup;
use App\Jobs\ProcessGuild;
use Illuminate\Http\Request;

use Illuminate\Database\Eloquent\ModelNotFoundException;

class GuildController extends Controller
{
    use Util\Squads;
    use Util\UpdatesRoles;

    public function listGuilds() {
        return view('guilds', [
            'schwartz' => Guild::where('schwartz', true)->orderBy('gp', 'desc')->get(),
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

    public function guildDiscordInfo() {
        return view('guild.profile', [
            'guilds' => auth()->user()->accounts->pluck('guild'),
        ]);
    }

    public function saveGuildInfo(Request $request, $guildID) {
        $validated = $request->validate([
            'value' => 'required|string',
        ]);

        $guild = Guild::findOrFail($guildID);
        Gate::authorize('edit-guild-profile', $guild);

        $prop = $request->get('prop');

        if (!in_array($prop, ['server_id', 'admin_channel', 'officer_role_regex', 'member_role_regex'])) {
            http_403("Bad prop");
        }

        $guild->$prop = $validated['value'];
        $guild->save();

        return response()->json(['success' => true]);
    }

    public function updateMembersFromRoles() {
        return $this->updateMemberList();
    }

    public function postGuildCompare(Request $request) {
        $validated = $request->validate([
            'guild1' => 'required|integer',
            'guild2' => 'required|integer',
        ]);

        return redirect()->route('guild.compare', $validated);
    }

    public function scrapeGuild($guild) {
        $guild = Guild::findOrFail($guild);

        ProcessGuild::dispatch($guild->guild_id);

        return redirect()->route('guilds')->with('guildStatus', "Guild scrape queued");
    }

    public function listMembers($guild, $team, $mode = 'guild', int $index = PHP_INT_MAX) {
        $guild = Guild::findOrFail($guild);

        if (ctype_digit(strval($team))) {
            $team = SquadGroup::findOrFail($team);
            Gate::authorize('view-squad', $team);
            $highlight = 'gear';
            $teams = $team->squads->mapWithKeys(function($squad) {
                return [$squad->display => collect([$squad->leader_id])->concat($squad->additional_members)->toArray()];
            });
            $teamKeys = $teams->keys();
        } else {
            list($highlight, $teams) = $this->getSquadsFor($team);
            $teamKeys = array_keys($teams);
        }

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
            'teamKeys' => collect($teamKeys)->map(function($k, $i) { return ['title' => $k, 'index' => $i]; }),
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
            $guild1 = Guild::where(['guild_id' => $first])->firstOrFail();
        }
        if (preg_match('/^\d{3}-?\d{3}-?\d{3}$/', $second)) {
            $ally2 = preg_replace('/[^0-9]/', '', $second);
            $member2 = Member::where(['ally_code' => $ally2])->firstOrFail();
            $guild2 = $member2->guild()->firstOrFail();
        } else {
            $guild2 = Guild::where(['guild_id' => $second])->firstOrFail();
        }

        if (in_array('not_scraped', [$guild1->url, $guild2->url])) {
            throw new ModelNotFoundException;
        }

        $compareData = Guild::getCompareData($guild1, $guild2);
        $g1 = $compareData->first();
        $g2 = $compareData->last();
        $g1_id = $g1['guild_id'];

        $winners = collect($g1)->mapWithKeys(function($g1Val, $key) use ($g1_id, $g2) {
            return [$key => $g1Val > $g2[$key] ? $g1_id : ($g1Val < $g2[$key] ? $g2['guild_id'] : 0)];
        });

        $g1RTotal = $g1['relic_7'] + $g1['relic_6'] + $g1['relic_5'];
        $g2RTotal = $g2['relic_7'] + $g2['relic_6'] + $g2['relic_5'];
        $winners['r_total'] = $g1RTotal > $g2RTotal ? $g1_id : ($g1RTotal < $g2RTotal ? $g2['guild_id'] : 0);

        $g1total = $g1['gear_13'] + $g1['gear_12'] + $g1['gear_11'];
        $g2total = $g2['gear_13'] + $g2['gear_12'] + $g2['gear_11'];
        $winners['g_total'] = $g1total > $g2total ? $g1_id : ($g1total < $g2total ? $g2['guild_id'] : 0);

        return view('guild.compare', [
            'character_list' => Guild::getCompareCharacters(),
            'data' => $compareData,
            'winner' => $winners,
        ]);
    }
}
