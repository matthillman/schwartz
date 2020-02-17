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
}
