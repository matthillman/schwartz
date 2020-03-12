<?php

namespace App\Http\Controllers;

use Gate;
use App\Unit;
use App\Guild;
use App\Member;
use App\SquadGroup;
use App\TerritoryWarPlan;

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
        $plan = TerritoryWarPlan::with('guild.members')->findOrFail($id);

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
        $plan = TerritoryWarPlan::with('guild.members')->findOrFail($id);

        Gate::authorize('in-guild', $plan->guild->id);

        $unitIDs = $plan->squad_group->squads->pluck('additional_members')->flatten()->merge($plan->squad_group->squads->pluck('leader_id'))->unique();

        return view('tw.assignments',[
            'plan' => $plan,
            'squads' => $plan->squad_group->squads->keyBy('id'),
            'member' => Member::where('ally_code', $allyCode)->firstOrFail()->characterSet($unitIDs->all()),
            'units' => Unit::whereIn('base_id', $unitIDs)->get()->keyBy('base_id'),
        ]);
    }

    function saveZone(Request $request, $plan, $zone) {
        $plan = TerritoryWarPlan::findOrFail($plan);

        Gate::authorize('edit-guild', $plan->guild->id);

        $plan->{"zone_{$zone}"} = json_decode($request->get('assignments', '{}'));
        $plan->{"zone_{$zone}_notes"} = $request->get('notes') ?: '';

        $plan->save();

        return response()->json(['success' => true]);
    }
}
