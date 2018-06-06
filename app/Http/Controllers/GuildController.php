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

    public function listMembers($guild, $team) {
        $guild = Guild::findOrFail($guild);

        $highlight = "gear";
        switch ($team) {
            case 'str':
                $teams = [
                    'RJT' => ['REYJEDITRAINING', 'BB8', 'R2D2_LEGENDARY', 'REY', 'RESISTANCETROOPER', 'VISASMARR', 'HERMITYODA'],
                    'Chex' => ['COMMANDERLUKESKYWALKER', 'HANSOLO', 'DEATHTROOPER', 'CHIRRUTIMWE', 'PAO', 'CT7567', 'ANAKINKNIGHT'],
                    'Nightsisters' => ['ASAJVENTRESS', 'DAKA', 'TALIA', 'NIGHTSISTERACOLYTE', 'NIGHTSISTERINITIATE', 'NIGHTSISTERZOMBIE', 'MOTHERTALZIN'],
                ];
                break;
            case 'rjt':
                $teams = [
                    'RJT' => ['REYJEDITRAINING', 'REY', 'BB8', 'FINN', 'SMUGGLERHAN', 'SMUGGLERCHEWBACCA'],
                ];
                $highlight = 'stars';
                break;

            default:
                $teams = [];
                break;
        }

        return view('members', [
            'members' => $guild->members()->with('characters')->orderBy('player')->get(),
            'units' => Unit::all(),
            'teams' => $teams,
            'highlight' => $highlight,
        ]);
    }
}
