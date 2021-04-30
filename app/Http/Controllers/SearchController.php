<?php

namespace App\Http\Controllers;

use App\Unit;
use App\Guild;
use App\Member;
use App\Category;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function searchGuilds(Request $request) {
        if (strlen($request->search)) {
            return Guild::search($request->search)
                ->where('schwartz', 'false')
                ->orderBy('gp', 'desc')
                ->paginate(config('view.page_size'));
        }

        return Guild::where('schwartz', 'false')
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

        return Unit::whereRaw('json_array_length(category_list) > 0')
            ->orderBy('name')
            ->paginate(50);
    }
    public function searchCategories(Request $request) {
        if (strlen($request->search)) {
            return Category::search($request->search)
                ->where('visible', 'true')
                ->paginate(50);
        }

        return Category::where('visible', 'true')
                ->orderBy('category_id')
                ->orderBy('description')
                ->paginate(50);
    }

    public function searchMemberUnits(Request $request, $ally) {
        $member = Member::where('ally_code', $ally)->firstOrFail();
        $unit = Unit::where('base_id', strtoupper($request->search))->where('combat_type', 1)->first();
        if (is_null($unit)) {
            $unit = Unit::search($request->search)->where('combat_type', 1)->first();
        }

        $u = $member->characters()->where('unit_name', $unit->base_id)->first();

        if ($u) {
            return response()->json($u);
        }

        abort(404);
    }
}
