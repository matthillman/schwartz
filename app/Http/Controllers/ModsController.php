<?php

namespace App\Http\Controllers;

use Artisan;
use App\Member;
use App\ModUser;
use App\Character;
use App\CharacterMod;
use App\Jobs\ProcessUser;
use Illuminate\Http\Request;

class ModsController extends Controller
{
    /**
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (auth()->check() && auth()->user()->active && $request->route()->named('mods')) {
            return redirect()->route('auth.mods');
        }
        return view('mods');
    }

    public function pullUser($user) {
        ProcessUser::dispatch($user);
    }

    public function modsFor($user) {
        return response()->json(ModUser::where('name', $user)->firstOrFail()->mods);
    }

    public function unitsFor($user) {
        return response()->json(
            Member::where('ally_code', $user)->firstOrFail()
                ->characters()
                ->where('combat_type', 1)
                ->get()
                ->sortBy('display_name')
                ->values()
                // ->mapWithKeys(function($unit) {
                //     return [$unit['unit_name'] => $unit];
                // })
        );
    }

    public function calculateStats() {
        $unitID = request()->input('unit');
        $modIDs = request()->input('mods');

        $unitData = Character::findOrFail($unitID)->rawData->data;
        $unitData['mods'] = collect($modIDs)->map(function($modID) {
            return CharacterMod::where('uid', $modID)->firstOrFail()->raw;
        })->toArray();

        $updated = stats()->addStatsTo([$unitData])->first();

        return $updated['stats'];
    }
}
