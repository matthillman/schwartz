<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'discord', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function updateFromOauthUser($user) {
        $this->discord = $user->getNickname();
        $this->name = $user->getName();
        $this->discord_id = $user->getId();
        $this->email = $user->getEmail();
        $this->avatar = $user->getAvatar();
        $this->token = $user->token;
        $this->refresh_token = $user->refreshToken;

        $this->discriminator = $user->user['discriminator'];

        if (is_null($this->password)) {
            $this->password = str_random(40);
        }

        $this->save();
    }
}
