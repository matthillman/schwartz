<?php

namespace App\Http\Controllers;

use Gate;
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
        $plan->save();
        $plan->squad_group()->associate($group);
        $plan->guild()->associate($group->guild);

        return redirect()->name('plan.edit', ['plan' => $plan->id]);
    }

    public function show(Request $request, $id) {
        $plan = TerritoryWarPlan::findOrFail($id);

        Gate::authorize('in-guild', $plan->guild->id);

        return view('tw.plan', ['plan' => $plan]);

    }
}
