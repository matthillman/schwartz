<?php

namespace App\Http\Controllers;

use Gate;
use App\TerritoryWarPlan;

use Illuminate\Http\Request;

class TerritoryWarPlanController extends Controller
{
    public function show(Request $request, $id) {
        $plan = TerritoryWarPlan::findOrFail($id);

        Gate::authorize('in-guild', $plan->guild->id);

        return view('tw.plan', ['plan' => $plan]);

    }
}
