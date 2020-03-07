<?php

namespace App\Http\Controllers;

use Gate;
use App\Unit;
use App\Guild;
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
        $plan = TerritoryWarPlan::findOrFail($id);

        Gate::authorize('in-guild', $plan->guild->id);

        return view('tw.plan', [
            'plan' => $plan,
            'unitIDs' => $plan->squad_group->squads->pluck('additional_members')->flatten()->merge($plan->squad_group->squads->pluck('leader_id'))->unique()->toArray(),
            'units' => Unit::all()->sortBy('name')->values(),
        ]);

    }
}
