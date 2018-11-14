<?php

namespace App\Http\Controllers;

use App\Unit;
use App\UnitModPreference;
use Illuminate\Http\Request;

class UnitModPreferenceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('unit-mods.list', [
            'units' => Unit::with('preference')
                ->where('combat_type', 'CHARACTER')
                ->orderBy('name')->get()
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\UnitModPreference  $unitModPreference
     * @return \Illuminate\Http\Response
     */
    public function show($baseID)
    {
        $baseID = strtoupper($baseID);

        return view('unit-mods.edit', [
            'preference' => UnitModPreference::firstOrNew(['unit_id' => $baseID])
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\UnitModPreference  $unitModPreference
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        
        return response()->json($request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\UnitModPreference  $unitModPreference
     * @return \Illuminate\Http\Response
     */
    public function destroy(UnitModPreference $unitModPreference)
    {
        //
    }
}
