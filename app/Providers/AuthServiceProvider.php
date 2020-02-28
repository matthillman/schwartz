<?php

namespace App\Providers;

use Auth;
use Socialite;
use App\Guild;
use App\SquadGroup;
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

        Gate::define('edit-global-squads', function ($user) {
            return $user->edit_teams;
        });

        Gate::define('edit-squad', function ($user, $squadGroup) {
            if (!($squadGroup instanceof SquadGroup)) {
                $squadGroup = SquadGroup::findOrFail($squadGroup);
            }
            if ($squadGroup->id == 1 || $squadGroup->guild_id === 0) { return $user->edit_teams; }

            return $user->accounts->pluck('guild')->pluck('id')->contains($squadGroup->guild_id);
        });

        Gate::define('edit-guild', function ($user, $guild) {
            if ($guild === 0) {
                return $user->admin || $user->edit_teams;
            }
            if (!($guild instanceof Guild)) {
                $guild = Guild::findOrFail($guild);
            }

            return $user->accounts->pluck('guild')->pluck('id')->contains($guild->id);
        });
    }
}
