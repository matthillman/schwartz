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

            return $user->accounts
                ->contains(function($account) use ($squadGroup, $user) {
                    if (!$account->guild || $account->guild->id != $squadGroup->guild_id) { return false; }
                    if (is_null($account->guild->server_id)) { return false; }
                    return collect($user->discord_roles->roles[$account->guild->server_id]['roles'])->first(function($role) use ($account) {
                        return preg_match($account->guild->officer_role_regex, $role['name']);
                    });
                });
        });

        Gate::define('edit-guild', function ($user, $guild) {
            if ($guild === 0) {
                return $user->admin || $user->edit_teams;
            }

            // if ($user->admin) { return true; }

            if (!($guild instanceof Guild)) {
                $guild = Guild::findOrFail($guild);
            }

            return $user->accounts
                ->contains(function($account) use ($guild, $user) {
                    if (!$account->guild || $account->guild->id != $guild->id) { return false; }
                    if (is_null($account->guild->server_id)) { return false; }
                    return collect($user->discord_roles->roles[$account->guild->server_id]['roles'])->first(function($role) use ($account) {
                        return preg_match($account->guild->officer_role_regex, $role['name']);
                    });
                });
        });

        Gate::define('in-guild', function ($user, $guild) {
            if ($user->admin) { return true; }

            if (!($guild instanceof Guild)) {
                $guild = Guild::findOrFail($guild);
            }

            return $user->accounts
                ->contains(function($account) use ($guild) {
                    return $account->guild && $account->guild->id == $guild->id;
                });
        });

        Gate::define('edit-guild-profile', function ($user, $guild) {
            if ($guild === 0) {
                return $user->admin;
            }
            if (!($guild instanceof Guild)) {
                $guild = Guild::findOrFail($guild);
            }

            return $user->accounts->filter(function ($account) use ($guild) {
                return $account->guild->id === $guild->id;
            })->reduce(function ($r, $account) {
                return $r || $account->member_level > 2;
            }, false);
        });
    }
}
