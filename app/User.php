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

    public function allyCodes() {
        return $this->hasMany(AllyCodeMap::class, 'discord_id', 'discord_id');
    }

    public function discord_roles() {
        return $this->hasOne(DiscordRole::class, 'discord_id', 'discord_id')->withDefault();
    }

    public function accounts() {
        return $this->hasManyThrough(Member::class, AllyCodeMap::class, 'discord_id', 'ally_code', 'discord_id', 'ally_code');
    }

    public function allyCodeForGuild($id = null) {
        $server = $this->allyCodes()->where('server_id', $id)->first();

        if (is_null($server)) {
            $server = $this->allyCodes()->whereNull('server_id')->first();
        }

        return is_null($server) ? null : $server->ally_code;
    }

    public function accountForGuild($id = null) {
        return Member::where(['ally_code' => $this->allyCodeForGuild($id)])->firstOrFail();
    }

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
            $this->active = $this->accounts->reduce(function($c, $m) { return $c || $m->guild->schwartz; }, false);
            $frax = User::where('discord_id', '297101898375364609')->first();
            if ($frax) {
                $frax->notify(new \App\Notifications\DiscordMessage("New user login: $this->name ($this->discord) ($this->active)"));
            }

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

    public function getCanEditSquadsAttribute() {
        if ($this->edit_teams) { return true; }

        return $this->accounts
            ->contains(function($account) {
                if (!$account->guild) { return false; }
                if (is_null($account->guild->server_id)) { return false; }
                return collect($this->discord_roles->roles[$account->guild->server_id]['roles'])->first(function($role) use ($account) {
                    return preg_match("/{$account->guild->officer_role_regex}/i", $role['name']);
                });
            });
    }

    public function routeNotificationForDiscord()
    {
        return $this->discord_private_channel_id;
    }
}
