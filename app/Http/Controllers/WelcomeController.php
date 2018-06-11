<?php

namespace App\Http\Controllers;

use App\Guild;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function __invoke() {
        return view('welcome', [
            'guilds' => Guild::where('schwartz', 1)->orderBy('gp', 'desc')->get()
        ]);
    }
}
