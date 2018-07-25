<?php

namespace App\Http\Controllers;

use App\TerritoryWarTeam;
use Illuminate\Http\Request;
use App\TerritoryWarTeamCounter;
use App\Http\Requests\StoreTWTeam;

class TerritoryCountersController extends Controller
{
    public function __construct() {
        $this->middleware(function ($request, $next) {
            if (auth()->user()->edit_tw) {
                return $next($request);
            }

            abort(403);
        })->only([
            'create'
        ]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $query = TerritoryWarTeam::with('counters')->orderBy('name');
        return view('territory-wars.teams', [
            'teams' => TerritoryWarTeam::with('counters')->orderBy('name')->get()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('territory-wars.edit-team', ['team' => new TerritoryWarTeam]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTWTeam $request)
    {
        $validated = $request->validated();
        \DB::transaction(function() use ($validated) {
            $team = new TerritoryWarTeam;
            $team->name = $validated['name'];
            $team->aliases = strtolower($validated['aliases']);
            $counters = collect($validated['counter'])
                ->zip($validated['notes'])
                ->map(function($counter) {
                    list($name, $note) = $counter;
                    return [ 'name' => $name, 'description' => $note];
                });

            $team->save();
            $team->counters()->createMany($counters->toArray());
        });

        $name = $validated['name'];
        return redirect()->route('tw-teams.index')->with('twStatus', "$name Added");
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\TerritoryWarTeam  $territoryWarTeam
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return view('territory-wars.edit-team', ['team' => TerritoryWarTeam::findOrFail($id)]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\StoreTWTeam  $request
     * @param  \App\TerritoryWarTeam  $territoryWarTeam
     * @return \Illuminate\Http\Response
     */
    public function update(StoreTWTeam $request, $id)
    {
        $validated = $request->validated();
        \DB::transaction(function() use ($validated, $id) {
            $team = TerritoryWarTeam::findOrFail($id);
            $team->name = $validated['name'];
            $team->aliases = strtolower($validated['aliases']);
            $counters = collect($validated['counter'])
                ->zip($validated['notes'])
                ->map(function($counter) {
                    list($name, $note) = $counter;
                    $team = TerritoryWarTeamCounter::firstOrNew([ 'name' => $name ]);
                    $team->description = $note;
                    return $team;
                });

            $team->save();
            $team->counters()->saveMany($counters);
            $team->counters()->whereNotIn('name', $validated['counter'])->delete();
        });
        $name = $validated['name'];
        return redirect()->route('tw-teams.index')->with('twStatus', "$name Updated");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\TerritoryWarTeam  $territoryWarTeam
     * @return \Illuminate\Http\Response
     */
    public function destroy(TerritoryWarTeam $territoryWarTeam)
    {
        //
    }
}
