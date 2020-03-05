<?php

namespace App\Http\Controllers;

use App\User;
use App\Recruit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    use Util\UpdatesRoles;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (auth('admin')->check()) {
            $users = User::where('active', false)->get();
            $recruits = Recruit::all();
        } else {
            $users = [];
            $recruits = [];
        }
        return view('home', [
            'userRequests' => $users,
            'recruits' => $recruits,
        ]);
    }

    /**
     * Show the waiting for approval page
     *
     * @return \Illuminate\Http\Response
     */
    public function waiting()
    {
        return view('waiting', ['updating' => false]);
    }

    public function updateRoles() {
        return $this->doRoleUpdate(null, true);
    }

    public function waitingOnRoleUpdate() {
        return view('waiting', ['updating' => true]);
    }

    public function notify(Request $request) {
        Auth::user()->notify(new \App\Notifications\DiscordMessage("This is a test message. 🍺"));

        return back()->withInput();
    }

    public function approveUser(Request $request, $id) {
        $user = User::findOrFail($id);
        $user->active = true;

        $user->save();

        return redirect()->route('home')->with('userStatus', "{$user->name} approved");
    }
    public function approveAdmin(Request $request, $id) {
        $user = User::findOrFail($id);
        $user->active = true;
        $user->admin = true;

        $user->save();

        return redirect()->route('home')->with('userStatus', "{$user->name} approved as admin");
    }
}
