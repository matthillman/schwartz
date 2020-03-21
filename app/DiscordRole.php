<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use NotificationChannels\Discord\Discord;

class DiscordRole extends Model
{
    const DM_FAILED = -1;
    const DM_NONE = 0;
    const DM_PENDING = 1;
    const DM_SUCCESS = 2;

    protected $fillable = [ 'discord_id' ];

    protected $casts = [
        'roles' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'discord_id', 'discord_id');
    }

    public function ally()
    {
        return $this->belongsTo(AllyCodeMap::class, 'discord_id', 'discord_id');
    }

    public function getDiscordNameAttribute() {
        if (is_null($this->username)) {
            return null;
        }
        return "{$this->username}#{$this->discriminator}";
    }

    public function getDiscordPrivateChannelIdAttribute($value) {
        if ($this->user) {
            return $this->user->discord_private_channel_id;
        }
        if (is_null($value)) {
            $channelID = app(Discord::class)->getPrivateChannel($this->discord_id);
            $this->discord_private_channel_id = $channelID;
            $this->save();

            return $channelID;
        }

        return $value;
    }
}
