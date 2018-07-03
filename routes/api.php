<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });
Route::middleware('client')->get('/tw/compare/{first}/{second}', function (Request $request, $first, $second) {
    $guild1 = \App\Guild::with('members.characters')->where(['guild_id' => $first])->firstOrFail();
    $guild2 = \App\Guild::with('members.characters')->where(['guild_id' => $second])->firstOrFail();

    $members1 = $guild1->members->reduce(function($data, $member) {
        return [
            'zetas' => $data['zetas'] + $member->zetas->count(),
            'gear_12' => $data['gear_12'] + $member->characters()->where('gear_level', 12)->count(),
            'gear_11' => $data['gear_11'] + $member->characters()->where('gear_level', 11)->count(),
        ];
    }, ['zetas' => 0, 'gear_12' => 0, 'gear_11' => 0]);
    $members2 = $guild2->members->reduce(function($data, $member) {
        return [
            'zetas' => $data['zetas'] + $member->zetas->count(),
            'gear_12' => $data['gear_12'] + $member->characters()->where('gear_level', 12)->count(),
            'gear_11' => $data['gear_11'] + $member->characters()->where('gear_level', 11)->count(),
        ];
    }, ['zetas' => 0, 'gear_12' => 0, 'gear_11' => 0]);

    return response()->json([
        $guild1->name => $members1,
        $guild2->name => $members2,
    ]);
});
