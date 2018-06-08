<?php

namespace App\Auth;

use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Auth\EloquentUserProvider as BaseProvider;

class EloquentUserProvider extends BaseProvider implements UserProvider
{
    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     *
     * @param  mixed  $identifier
     * @param  string  $token
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByToken($identifier, $token)
    {
        $model = parent::retrieveByToken($identifier, $token);

        if ($model->token) {
            $discordUser = Socialite::driver('discord')->userFromToken($model->token);

            if ($discordUser) {
                $model->updateFromOauthUser($discordUser);

                return $model;
            }
        }

        return null;
    }
}