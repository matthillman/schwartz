<?php

namespace App\Http\Controllers;

use App\User;
use App\Mods\ModsParser;
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
        $parser = new ModsParser($user);
        $parser->scrape();

        return response()->json($parser->mods);
    }
}
