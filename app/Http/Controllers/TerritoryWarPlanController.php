<?php

namespace App\Http\Controllers;

use Gate;
use App\Unit;
use App\Guild;
use App\Member;
use App\SquadGroup;
use App\DiscordRole;
use App\TerritoryWarPlan;
use App\Events\TWPlanChanged;
use App\Events\TWPlanUserState;

use Illuminate\Http\Request;

class TerritoryWarPlanController extends Controller
{
    public function createFrom(Request $request, $squadID) {
        $group = SquadGroup::findOrFail($squadID);

        Gate::authorize('edit-squad', $group);

        $plan = new TerritoryWarPlan;
        $plan->name = $request->get('name', 'New TW Plan 🍺');
        $plan->squad_group_id = $group->id;
        $plan->guild_id = $group->guild->id;
        $plan->save();

        return response()->json([
            'route' => route('tw-plan.edit', ['plan' => $plan->id]),
        ]);
    }

    public function show(Request $request, $id) {
        $plan = TerritoryWarPlan::with('guild.members.stats')->findOrFail($id);

        Gate::authorize('in-guild', $plan->guild->id);

        $units = Unit::all()->sortBy('name')->values();

        return view('tw.plan', [
            'plan' => $plan,
            'squads' => $plan->squad_group->squads->keyBy('id'),
            'unitIDs' => $plan->squad_group->squads->pluck('additional_members')->flatten()->merge($plan->squad_group->squads->pluck('leader_id'))->unique()->toArray(),
            'units' => $units,
        ]);
    }

    public function showAssignment(Request $request, $id, $allyCode) {
        $plan = TerritoryWarPlan::with('guild.members.stats')->findOrFail($id);

        if (auth()->user()) {
            Gate::authorize('in-guild', $plan->guild->id);
        }

        $unitIDs = $plan->squad_group->squads->pluck('additional_members')->flatten()->merge($plan->squad_group->squads->pluck('leader_id'))->unique();

        return view('tw.assignments',[
            'plan' => $plan,
            'squads' => $plan->squad_group->squads->keyBy('id'),
            'member' => Member::where('ally_code', $allyCode)->firstOrFail()->characterSet($unitIDs->all()),
            'units' => Unit::whereIn('base_id', $unitIDs)->get()->keyBy('base_id'),
        ]);
    }

    function saveZone(Request $request, $id, $zone) {
        $plan = TerritoryWarPlan::findOrFail($id);

        Gate::authorize('edit-guild', $plan->guild->id);

        $plan->{"zone_{$zone}"} = json_decode($request->get('assignments', '{}'));
        $plan->{"zone_{$zone}_notes"} = $request->get('notes') ?: '';

        $plan->save();

        broadcast(new TWPlanChanged($plan, $zone, $request->input()))->toOthers();

        return response()->json(['success' => true]);
    }

    public function publishUserState(Request $request, $id) {

        $plan = TerritoryWarPlan::findOrFail($id);

        Gate::authorize('edit-guild', $plan->guild->id);

        broadcast(new TWPlanUserState($plan, $request->input()))->toOthers();

        return response()->json(['success' => true]);
    }

    public function sendDMs(Request $request, $plan) {
        $plan = TerritoryWarPlan::findOrFail($plan);
        Gate::authorize('edit-guild', $plan->guild->id);

        $members = collect(explode(',', $request->get('members')))
            ->map(function($ally) {
                return Member::with('roles')->where(['ally_code' => str_replace('-', '', $ally)])->firstOrFail();
            })
            ->filter(function($member) {
                return !is_null($member->roles->discord_id);
            })
        ;

        if ($members->count() == 0) {
            return http_500("No members to DM");
        }

        $members->each(function($member) use ($plan) {
            $member->roles->dm_status = DiscordRole::DM_PENDING;
            $member->push();
            broadcast(new \App\Events\DMState($plan, ['ally_code' => $member->ally_code, 'dm_status' => $member->roles->dm_status]));
        });

        broadcast(new \App\Events\BotCommand([
            'command' => 'send-dms',
            'members' => $members->map(function ($member) { return [ 'ally_code' => $member->ally_code, 'id' => $member->roles->discord_id ]; })->values(),
            'url' => "twp/{$plan->id}/member",
            'message' => 'Here are your defensive assignments for this TW! Please ask if you have any questions!',
            'tag' => ['dm', "plan:{$plan->id}"],
            'context' => "$plan->id",
        ]));

        return response()->json(['success' => true]);
    }
}
