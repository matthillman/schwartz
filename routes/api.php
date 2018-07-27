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
    $guild1 = \App\Guild::where(['guild_id' => $first])->firstOrFail();
    $guild2 = \App\Guild::where(['guild_id' => $second])->firstOrFail();

    $data = DB::table('guilds')
        ->join('members', 'members.guild_id', '=', 'guilds.id')
        ->join('characters', 'characters.member_id', '=', 'members.id')
        ->join('character_zeta', 'character_zeta.character_id', '=', 'characters.id')
        ->selectRaw("count('zeta_id') as zetas, sum(case when characters.gear_level = 12 then 1 else 0 end) as gear_12, sum(case when characters.gear_level = 11 then 1 else 0 end) as gear_11, guilds.guild_id")
        ->groupBy('guilds.guild_id')
        ->whereIn('guilds.guild_id', [$guild1->guild_id, $guild2->guild_id])
        ->get();

    $g1Data = $data->firstWhere('guild_id', $guild1->guild_id);
    $g2Data = $data->firstWhere('guild_id', $guild2->guild_id);

    return response()->json([
        $guild1->name => $g1Data,
        $guild2->name => $g2Data,
    ]);
});
