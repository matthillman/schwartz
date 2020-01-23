<?php

namespace App\Events;

use App\Guild;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class GuildProcessed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
    * The name of the queue on which to place the event.
    *
    * @var string
    */
    public $broadcastQueue = 'notifications';

    protected $guild;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($guild)
    {
        $this->guild = $guild;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('guilds');
    }

    public function broadcastAs() {
        return 'guild.fetched';
    }

    public function broadcastWith() {
        return [
            'guild' => [
                'name' => $this->guild->name,
                'id' => $this->guild->id,
                'guild_id' => $this->guild->guild_id,
            ]
        ];
    }

    public function tags() {
        return ['fetched', 'guild:' . $this->guild->guild_id];
    }
}
