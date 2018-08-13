<?php

namespace App\Providers;

use Horizon;
use App\Database\UpsertBuilder;
use Illuminate\Support\Facades\Blade;
use Illuminate\Database\Query\Builder;
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
        Builder::macro('upsert', function(array $values, $conflict) {
            $builder = new UpsertBuilder($this);
            return $this->connection->insert($builder->getQuery($values, $conflict));
        });
    }
}
