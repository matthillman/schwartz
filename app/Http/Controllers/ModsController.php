<?php

namespace App\Http\Controllers;

use Artisan;
use App\User;
use App\ModUser;
use Carbon\Carbon;
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
        $needsScrape = ModUser::where('name', $user)
            ->whereDate('last_scrape', Carbon::now())
            ->whereTime('last_scrape', '>', Carbon::now()->subMinutes(30))
            ->doesntExist();

        if ($needsScrape) {
            Artisan::call('mods:pull', [
                'user' => $user
            ]);
        }

        return response()->json(ModUser::where('name', $user)->firstOrFail()->mods);
    }
}
