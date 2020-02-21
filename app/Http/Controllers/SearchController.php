<?php

namespace App\Http\Controllers;

use App\Unit;
use App\Guild;
use App\Member;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function searchGuilds(Request $request) {
        if (strlen($request->search)) {
            return Guild::search($request->search)
                ->where('schwartz', false)
                ->orderBy('gp', 'desc')
                ->paginate(config('view.page_size'));
        }

        return Guild::where('schwartz', false)
                ->orderBy('gp', 'desc')
                ->paginate(config('view.page_size'));
    }

    public function searchMembers(Request $request) {
        if (strlen($request->search)) {
            return Member::search($request->search)
                ->orderBy('gp', 'desc')
                ->paginate(config('view.page_size'));
        }

        return Member::orderBy('gp', 'desc')
                ->paginate(config('view.page_size'));
    }

    public function searchUnits(Request $request) {
        if (strlen($request->search)) {
            return Unit::search($request->search)
                ->paginate(50);
        }

        return Unit::orderBy('name')
                ->paginate(50);
    }

    public function searchMemberUnits(Request $request, $ally) {
        $member = Member::where('ally_code', $ally)->firstOrFail();
        $unit = Unit::where('base_id', strtoupper($request->search))->where('combat_type', 1)->first();
        if (is_null($unit)) {
            $unit = Unit::search($request->search)->where('combat_type', 1)->first();
        }

        return response()->json($member->characters()->where('unit_name', $unit->base_id)->firstOrFail());
    }
}