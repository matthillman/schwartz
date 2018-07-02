<?php

namespace App\Providers;

use Auth;
use Socialite;
use App\Auth\DiscordProvider;
use Laravel\Passport\Passport;
use App\Auth\EloquentUserProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Socialite::extend('discord', function() {
            $config = $this->app['config']['services.discord'];
            return Socialite::buildProvider(
                DiscordProvider::class, $config
            );
        });

        Auth::provider('eloquent_discord', function($app, array $config) {
            return new EloquentUserProvider($app['hash'], $config['model']);
        });

        Passport::routes();

        Passport::tokensCan([
            'bot' => 'Schwartz bot',
        ]);
    }
}
