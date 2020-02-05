<?php

namespace App;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use NotificationChannels\Discord\Discord;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

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

        if (!$this->exists) {
            User::where('discord_id', '297101898375364609')
                ->notify(new \App\Notifications\DiscordMessage("New user login: $this->name ($this->discord)"));
        }

        $this->save();
    }

    public function getDiscordPrivateChannelIdAttribute($value) {
        if (is_null($value)) {
            $channelID = app(Discord::class)->getPrivateChannel($this->discord_id);
            $this->discord_private_channel_id = $channelID;
            $this->save();

            return $channelID;
        }

        return $value;
    }

    public function routeNotificationForDiscord()
    {
        return $this->discord_private_channel_id;
    }
}
