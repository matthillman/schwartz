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
        $group = SquadGroup::findOrFail($request->get('group', 1));

        Gate::authorize('edit-squad', $group);

        $squads = $group->squads()->orderBy('display')->get();
        $units = Unit::all()->sortBy('name')->values();

        list($chars, $ships) = $squads->partition(function ($squad) use ($units) {
            return $units->where('base_id', $squad->leader_id)->first()->combat_type == 1;
        });

        $guildIDs = auth()->user()->accounts->pluck('guild')->pluck('id');
        $guilds = auth()->user()->accounts
            ->filter(function($account) {
                if (!$account->guild  || is_null($account->guild->server_id)) { return false; }
                return collect(auth()->user()->discord_roles->roles[$account->guild->server_id]['roles'])->first(function($role) {
                    return preg_match('/^officer/i', $role['name']);
                });
            })
            ->map(function ($a) { return ['label' => $a->guild_name, 'value' => $a->guild->id ]; });


        if (Gate::authorize('edit-global-squads')) {
            $guildIDs->prepend(0);
            $guilds->prepend([
                'label' => 'Global Squad',
                'value' => 0,
            ]);
        }

        $tabs = SquadGroup::whereIn('guild_id', $guildIDs)
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

        return view('squads.list', [
            'groups' => $tabs,
            'squad' => $group,
            'ships' => $ships,
            'chars' => $chars,
            'units' => $units,
            'guilds' => $guilds,
        ]);
    }

    public function add(Request $request) {
        Gate::authorize('edit-squad', $request->group);

        $squad = new Squad;

        $squad->leader_id = $request->leader_id;
        $squad->display = $request->name;
        $squad->description = $request->description;
        $squad->additional_members = array_filter(explode(',', $request->other_members ?: ''));
        $squad->squad_group_id = $request->group;

        $squad->save();

        return redirect()->route('squads', ['group' => $request->group])->with('status', "Squad added");
    }

    public function delete($id) {
        $squad = Squad::findOrFail($id);
        $squad->delete();

        return redirect()->route('squads')->with('status', "Squad deleted");
    }

    public function addGroup(Request $request) {
        $validated = $request->validate([
            'name' => 'required|string',
            'guild' => 'integer',
        ]);

        Gate::authorize('edit-guild', array_get($validated, 'guild', 0));

        $group = new SquadGroup;

        $group->name = $validated['name'];
        $group->guild_id = array_get($validated, 'guild', 0);

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
            $others = implode(' ', explode(',', $squad->additional_members));
            $message = "!addteam {$squad->leader_id} {$squad->display} 50 [{$squad->description}] {$others}";

            $discord->send($channel, ['content' => $message]);
        }

        $count = count($squadIds);
        return redirect()->route('squads')->with('status', "Messages sent for $count squads");;
    }
}
