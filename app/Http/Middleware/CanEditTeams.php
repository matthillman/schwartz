<?php

namespace App\Http\Middleware;

use Closure;

class CanEditTeams
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!auth()->user()->edit_teams) {
            return redirect('/home');
        }

        return $next($request);
    }
}
