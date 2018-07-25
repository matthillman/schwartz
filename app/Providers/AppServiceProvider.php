<?php

namespace App\Providers;

use Horizon;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Horizon::auth(function ($request) {
            $user = auth()->user();
            return $user && $user->admin;
        });
        Blade::if('user', function ($permission) {
            return auth()->user()->$permission;
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
