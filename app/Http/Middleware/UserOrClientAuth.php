<?php

namespace App\Http\Middleware;

use Closure;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate;
use Laravel\Passport\Http\Middleware\CheckClientCredentials;

class UserOrClientAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string[]  ...$guards
     * @return mixed
     */
    public function handle($request, Closure $next, ...$guards)
    {
        $configuredGuards = collect(config('auth.guards'))->keys();
        list($webParams, $clientParams) = collect($guards)->partition(function($g) use ($configuredGuards) {
            return $configuredGuards->contains($g);
        });
        $webParams->prepend($next);
        $clientParams->prepend($next);
        $webParams->prepend($request);
        $clientParams->prepend($request);

        try {
            return call_user_func_array([app(CheckClientCredentials::class), __FUNCTION__], $clientParams->all());
        } catch (AuthenticationException $e) {
            return call_user_func_array([app(Authenticate::class), __FUNCTION__], $webParams->all());
        }
    }
}
