<?php

namespace App\Http\Controllers;

use Artisan;
use App\Unit;
use App\Guild;
use Illuminate\Http\Request;

class GuildController extends Controller
{
    public function listGuilds() {
        return view('guilds', [
            'guilds' => Guild::orderBy('schwartz', 'desc')->orderBy('gp', 'desc')->get()
        ]);
    }

    public function addGuild(Request $request) {
        $validated = $request->validate([
            'guild' => 'required|integer'
        ]);

        Artisan::call('pull:guild', [
            'guild' => $validated['guild']
        ]);

        return redirect()->route('guilds')->with('guildStatus', "Guild added");
    }

    public function listMembers($guild) {
        $guild = Guild::findOrFail($guild);

        return view('members', [
            'members' => $guild->members()->with('characters')->orderBy('player')->get(),
            'units' => Unit::all()
        ]);
    }
}
