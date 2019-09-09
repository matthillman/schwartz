<?php

use App\Guild;
use App\Member;
use App\Jobs\ProcessGuild;
use Illuminate\Http\Request;
use App\Jobs\ProcessGuildAlly;

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

Route::middleware('client')->get('/guild/scrape/{id}', function(Request $request, $id) {
    $isAllyCode = preg_match('/^\d{3}-?\d{3}-?\d{3}$/', $id);
    if ($isAllyCode) {
        $id = preg_replace('/[^0-9]/', '', $id);
        ProcessGuildAlly::dispatch($id);
    } else {
        ProcessGuild::dispatch($id);
    }
    return response()->json([]);
});

Route::middleware('client')->get('/tw/compare/{first}/{second}', function (Request $request, $first, $second) {
    if (preg_match('/^\d{3}-?\d{3}-?\d{3}$/', $first)) {
        $ally = preg_replace('/[^0-9]/', '', $first);
        $member = Member::where(['ally_code' => $ally])->first();
        $guild1 = is_null($member) ? null : $member->guild;
    } else {
        $guild1 = \App\Guild::where(['guild_id' => $first])->first();
    }
    if (preg_match('/^\d{3}-?\d{3}-?\d{3}$/', $second)) {
        $ally2 = preg_replace('/[^0-9]/', '', $second);
        $member2 = Member::where(['ally_code' => $ally2])->first();
        $guild2 = is_null($member2) ? null : $member2->guild;
    } else {
        $guild2 = \App\Guild::where(['guild_id' => $second])->first();
    }

    if (is_null($guild1) || is_null($guild2)) {
        $response = [
            'error' => 'Missing at least 1 guild',
        ];
        $response[$first] = is_null($guild1);
        $response[$second] = is_null($guild2);

        return response()->json($response);
    }

    $chars = [
        'DARTHTRAYA' =>          'Traya',
        'DARTHREVAN' =>          'Darth Revan',
        'DARTHMALAK' =>          'Malak',
        'JEDIKNIGHTREVAN' =>     'Revan',
        'PADMEAMIDALA' =>        'Padmé',
        'GRIEVOUS' =>            'Grievous',
        'GEONOSIANBROODALPHA' => 'Geo Alpha',
    ];

    $unitQueries = collect($chars)->map(function($name, $unitName) {
        return [
            "sum(case when characters.unit_name = '${unitName}' then 1 else 0 end) as ${unitName}",
            "sum(case when characters.unit_name = '${unitName}' AND characters.gear_level = 12 then 1 else 0 end) as ${unitName}_12",
            "sum(case when characters.unit_name = '${unitName}' AND characters.gear_level = 13 then 1 else 0 end) as ${unitName}_13",
        ];
    })->collapse()->implode(', '); // FIXME ->join when upgrading laravel

    $data = DB::table('guilds') ->join('members', 'members.guild_id', '=', 'guilds.id') ->join('characters', 'characters.member_id', '=', 'members.id') ->selectRaw("
            guilds.guild_id,
            max(guilds.gp) as gp,
            count(distinct members.id) as member_count,
            sum(case when characters.gear_level = 13 then 1 else 0 end) as gear_13,
            sum(case when characters.gear_level = 12 then 1 else 0 end) as gear_12,
            sum(case when characters.gear_level = 11 then 1 else 0 end) as gear_11,
            ${unitQueries}
        ") ->groupBy('guilds.guild_id')
        ->whereIn('guilds.guild_id', [$guild1->guild_id, $guild2->guild_id])
        ->get();

    $g1Data = (array)$data->firstWhere('guild_id', $guild1->guild_id);
    $g2Data = (array)$data->firstWhere('guild_id', $guild2->guild_id);

    $zetas = DB::table('character_zeta')
        ->join('characters', 'character_id', '=', 'characters.id')
        ->join('members', 'characters.member_id', '=', 'members.id')
        ->selectRaw('count(1) as zetas, members.guild_id')
        ->groupBy('members.guild_id')
        ->whereIn('members.guild_id', [$guild1->id, $guild2->id])
        ->get();

    $g1Zetas = (array)$zetas->firstWhere('guild_id', $guild1->id);
    $g2Zetas = (array)$zetas->firstWhere('guild_id', $guild2->id);

    $g1Data['zetas'] = $g1Zetas['zetas'];
    $g2Data['zetas'] = $g2Zetas['zetas'];

    $mods = DB::table('guilds')
        ->join('members', 'members.guild_id', '=', 'guilds.id')
        ->join('mod_users', 'mod_users.name', '=', 'members.ally_code')
        ->join('mod_stats', 'mod_stats.mod_user_id', '=', 'mod_users.id')
        ->selectRaw("
            guilds.guild_id,
            sum(case when pips = 6 then 1 else 0 end) as six_dot,
            sum(case when speed >= 10 then 1 else 0 end) as ten_plus,
            sum(case when speed >= 15 then 1 else 0 end) as fifteen_plus,
            sum(case when speed >= 20 then 1 else 0 end) as twenty_plus,
            sum(case when speed >= 25 then 1 else 0 end) as twenty_five_plus,
            sum(case when offense >= 100 then 1 else 0 end) as one_hundred_offense
        ") ->groupBy('guilds.guild_id')
        ->whereIn('guilds.guild_id', [$guild1->guild_id, $guild2->guild_id])
        ->get();

    $g1Mods = (array)$mods->firstWhere('guild_id', $guild1->guild_id);
    $g2Mods = (array)$mods->firstWhere('guild_id', $guild2->guild_id);

    $g1Data['mods'] = $g1Mods;
    $g2Data['mods'] = $g2Mods;

    return response()->json([
        $guild1->name => $g1Data,
        $guild2->name => $g2Data,
        'char_keys' => collect($chars)->map(function($name, $unit) { return strtolower($unit); })->values()->toArray(),
        'char_names' => collect($chars)->mapWithKeys(function($name, $unit) { return [strtolower($unit) => $name]; })->toArray(),
        'mod_keys' => [
            'six_dot' => '6*',
            'ten_plus' => '10+',
            'fifteen_plus' => '15+',
            'twenty_plus' => '20+',
            'twenty_five_plus' => '25+',
            'one_hundred_offense' => '100+ Off',
        ],
    ]);
});
