<?php

namespace App\Http\Controllers;

use App\Guild;
use App\Recruit;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function index() {
        return view('welcome', [
            'guilds' => Guild::where('schwartz', 1)->orderBy('gp', 'desc')->get()
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

        return redirect()->route('welcome');
    }
}
