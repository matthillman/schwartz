<?php

namespace App\Http\Controllers\Auth;

use Socialite;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return 'discord';
    }

    protected function attemptingUserIsAdmin() {
        $user = User::where($this->username(), request($this->username()))->first();
        return $user && $user->admin;
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard($this->attemptingUserIsAdmin() ? 'admin' : null);
    }

    /**
     * Redirect the user to the GitHub authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToProvider()
    {
        return Socialite::driver('discord')->redirect();
    }

    /**
     * Obtain the user information from GitHub.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback()
    {
        $user = Socialite::driver('discord')->user();

        $auth_user = User::where('discord_id', $user->getId())->first();

        if (!$auth_user) {
            $auth_user = User::firstOrNew([$this->username() => $user->getNickname()]);
        }

        $auth_user->updateFromOauthUser($user);

        $guard = $auth_user->admin ? 'admin' : null;

        Auth::guard($guard)->login($auth_user, true);

        return redirect()->intended('home');
    }
}
