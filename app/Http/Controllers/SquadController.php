<?php

namespace App\Http\Controllers;

use App\Unit;
use App\Squad;
use Illuminate\Http\Request;

use NotificationChannels\Discord\Discord;

class SquadController extends Controller
{
    public function __construct() {
        $this->middleware('edit.teams');
    }

    public function index(Request $request) {
        return view('squads.list', [
            'squads' => Squad::orderBy('display')->get(),
            'units' => Unit::where('combat_type', 1)->get()->sortBy('name')->values(),
        ]);
    }

    public function add(Request $request) {
        $squad = new Squad;

        $squad->leader_id = $request->leader_id;
        $squad->display = $request->name;
        $squad->description = $request->description;
        $squad->additional_members = $request->other_members;

        $squad->save();

        return redirect()->route('squads')->with('status', "Squad added");
    }

    public function delete($id) {
        $squad = Squad::findOrFail($id);
        $squad->delete();

        return redirect()->route('squads')->with('status', "Squad deleted");
    }

    public function sendDiscordMessages(Request $request, $channel) {
        $discord = app(Discord::class);

        $squadIds = explode(',', $request->squads);
        foreach (Squad::whereIn('id', $squadIds)->get() as $squad) {
            $others = implode(' ', explode(',', $squad->additional_members));
            $message = "!addteam {$squad->leader_id} {$squad->display} 50 [{$squad->description}] {$others}";

            $discord->send($channel, ['content' => $message]);
        }

        $count = count($squadIds);
        return redirect()->route('squads')->with('status', "Messages sent for $count squads");;
    }
}
