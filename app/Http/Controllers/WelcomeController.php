<?php

namespace App\Http\Controllers;

use App\Guild;
use App\Recruit;
use App\Util\SchwartzSheetData;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function index() {
        $sheetData = new SchwartzSheetData;
        $guilds = Guild::where('schwartz', 1)->orderBy('gp', 'desc')->get()->map(function($guild) use ($sheetData) {
            $guildData = $sheetData->data($guild->guild_id);
            $guild->tb = $guildData->get('tb');
            $guild->stars = $guildData->get('stars');
            $guild->focus = $guildData->get('focus');
            $guild->raids = $guildData->get('raids');
            return $guild;
        });
        return view('welcome', [
            'guilds' => $guilds,
        ]);
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'discord' => 'required|unique:recruits',
            'url' => 'required|url',
            'referral' => 'string',
            'pitch' => 'string',
        ]);

        $recruit = new Recruit;
        $recruit->discord = $validated['discord'];
        $recruit->url = $validated['url'];
        $recruit->referral = $validated['referral'];
        $recruit->pitch = $validated['pitch'];
        $recruit->save();

        return redirect()->route('welcome', ['#join'])->with('inquireStatus', 'Thank you for your interest. Someone will be contacting you on Discord!');
    }
}
