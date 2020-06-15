<?php

namespace App\Http\Controllers;

use Gate;
use App\Unit;
use App\Squad;
use App\SquadGroup;
use Illuminate\Http\Request;

use NotificationChannels\Discord\Discord;

class SquadController extends Controller
{
    public function index(Request $request) {
        $guildIDs = auth()->user()->accounts->pluck('guild')->pluck('id');
        $guilds = auth()->user()->accounts
            ->filter(function($account) {
                if (!$account->guild  || is_null($account->guild->server_id)) { return false; }
                return collect(auth()->user()->discord_roles->roles[$account->guild->server_id]['roles'])->first(function($role) use ($account) {
                    return preg_match("/{$account->guild->officer_role_regex}/i", $role['name']);
                });
            })
            ->map(function ($a) { return ['label' => $a->guild_name, 'value' => $a->guild->id ]; })
        ;

        if (Gate::allows('edit-global-squads')) {
            $guildIDs->prepend(0);
            $guilds->prepend([
                'label' => 'Global Squad',
                'value' => 0,
            ]);
        }

        $guildIDs->prepend(-1);
        $guilds->prepend([
            'label' => 'Personal Squad',
            'value' => -1,
        ]);

        $group = SquadGroup::findOrFail($request->get('group', 1));

        if (Gate::denies('edit-squad', $group)) {
            if ($guildIDs->count() > 0) {
                $group = SquadGroup::whereIn('guild_id', $guildIDs)->first();
            } else {
                $group = SquadGroup::where('user_id', auth()->user()->id)->first();
            }
        }

        $units = Unit::all()->sortBy('name')->values();

        if (!is_null($group)) {
            $squads = $group->squads()->orderBy('display')->get();

            list($chars, $ships) = $squads->partition(function ($squad) use ($units) {
                return $units->where('base_id', $squad->leader_id)->first()->combat_type == 1;
            });
        } else {
            $chars = [];
            $ships = [];
        }

        $tabs = SquadGroup::whereIn('guild_id', $guildIDs->filter(function ($v) { return $v > -1; }))
            ->orWhere('user_id', auth()->user()->id)
            ->orderBy('guild_id')
            ->orderByDesc('publish')
            ->orderBy('name')->get()
            ->filter(function($squad) {
                return Gate::forUser(auth()->user())->allows('edit-squad', $squad);
            })
            ->map(function($g) { return [ 'title' => $g->name, 'index' => $g->id ]; });

        $tabs->prepend([
            'title' => 'Edit Groups',
            'icon' => 'pencil-sharp',
            'iconOnly' => true,
            'index' => -2,
        ]);

        $tabs->prepend([
            'title' => 'New Squad Group',
            'icon' => 'add-circle-outline',
            'iconOnly' => true,
            'index' => -1,
        ]);

        $editSquad = Squad::findOrNew($request->get('squad'));

        $chars = collect($chars)->sort(function($a, $b) {
            static $glList = ['GLREY', 'SUPREMELEADERKYLOREN'];
            static $metaList = ['GENERALSKYWALKER', 'JEDIKNIGHTREVAN', 'DARTHREVAN', 'GRIEVOUS', 'PADMEAMIDALA'];

            $aIsGL = in_array($a->leader_id, $glList);
            $bIsGL = in_array($b->leader_id, $glList);

            if ($aIsGL && !$bIsGL) {
                 return -1;
            } else if (!$aIsGL && $bIsGL) {
                return 1;
            }

            $aIsMeta = in_array($a->leader_id, $metaList);
            $bIsMeta = in_array($b->leader_id, $metaList);

            if ($aIsMeta && !$bIsMeta) {
                 return -1;
            } else if (!$aIsMeta && $bIsMeta) {
                return 1;
            }

            return strcasecmp($a->leader_id, $b->leader_id);
        })->values();

        return view('squads.list', [
            'groups' => $tabs,
            'group' => $group,
            'ships' => $ships,
            'chars' => $chars,
            'units' => $units,
            'guilds' => $guilds,
            'edit_squad' => $editSquad,
        ]);
    }

    public function getSquads($id) {
        $group = SquadGroup::with('squads')->findOrFail($id);

        Gate::authorize('view-squad', $group);

        $squads = $group->squads;
        $unitIDs = $squads->pluck('additional_members')->flatten()->merge($squads->pluck('leader_id'))->unique()->toArray();

        return response()->json([
            'squads' => $squads->keyBy('id'),
            'units' => Unit::whereIn('base_id', $unitIDs)->get()->sortBy('name')->keyBy('base_id'),
        ]);
    }

    public function add(Request $request) {
        Gate::authorize('edit-squad', $request->group);

        $squad = !empty($request->get('id')) ? Squad::findOrFail($request->get('id')) : new Squad;

        $squad->leader_id = $request->leader_id;
        $squad->display = $request->name;
        $squad->description = $request->description;
        $squad->additional_members = array_values(array_filter(explode(',', $request->get('other_members', ''))));
        $squad->squad_group_id = $request->group;

        $squad->save();

        return back()->with('status', $request->has('id') ? "Squad saved" : "Squad added");
        // return redirect()->route(previous_route_name(), ['group' => $request->group])->with('status', $request->has('id') ? "Squad saved" : "Squad added");
    }

    public function delete($id) {
        $squad = Squad::findOrFail($id);
        $group = $squad->squad_group_id;
        Gate::authorize('edit-squad', $group);
        $squad->delete();

        return redirect()->route(previous_route_name(), ['group' => $group])->with('status', "Squad deleted");
    }

    public function updateStats(Request $request, $id) {
        $squad = Squad::findOrFail($id);
        Gate::authorize('edit-squad', $squad->squad_group_id);

        $validated = $request->validate([
            'stats' => 'required',
        ]);

        $squad->stats = $validated['stats'];
        $squad->save();

        return response()->json(['success' => true]);
    }

    public function addGroup(Request $request) {
        $validated = $request->validate([
            'name' => 'required|string',
            'guild' => 'integer',
        ]);

        $guildID = array_get($validated, 'guild', 0);

        if ($guildID >= 0) {
            Gate::authorize('edit-guild', $guildID);
        }

        $group = new SquadGroup;

        $group->name = $validated['name'];
        $group->guild_id = $guildID;
        if ($guildID === -1) {
            $group->user_id = auth()->user()->id;
        }

        $group->save();

        return response()->json($group);
    }

    public function putGroup(Request $request, $squadGroup) {
        $validated = $request->validate([
            'value' => 'required|string',
        ]);

        $group = SquadGroup::findOrFail($squadGroup);
        Gate::authorize('edit-squad', $group);

        $group->name = $validated['value'];
        $group->save();

        return response()->json(['success' => true]);
    }

    public function deleteGroup(Request $request, $squadGroup) {
        $group = SquadGroup::findOrFail($squadGroup);
        Gate::authorize('edit-squad', $group);

        $group->delete();

        $request->session()->flash('status', "Squad group deleted");
        return response()->json(['success' => true]);
    }

    public function publish(Request $request, $squadGroup) {
        $validated = $request->validate([
            'value' => 'required|boolean',
        ]);

        $group = SquadGroup::findOrFail($squadGroup);
        Gate::authorize('edit-squad', $group);

        $group->publish = $validated['value'];
        $group->save();

        return response()->json(['success' => true]);
    }

    public function sendDiscordMessages(Request $request, $channel) {
        $discord = app(Discord::class);

        $squadIds = explode(',', $request->squads);
        foreach (Squad::whereIn('id', $squadIds)->get() as $squad) {
            $others = implode(' ', $squad->additional_members);
            $message = "!addteam {$squad->leader_id} {$squad->display} 50 [{$squad->description}] {$others}";

            $discord->send($channel, ['content' => $message]);
        }

        $count = count($squadIds);
        return redirect()->route('squads')->with('status', "Messages sent for $count squads");;
    }
}
