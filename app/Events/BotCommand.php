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

class BotCommand implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
    * The name of the queue on which to place the event.
    *
    * @var string
    */
    public $broadcastQueue = 'bot';

    protected $payload;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($payload)
    {
        $this->payload = $payload;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('bot-commands');
    }

    public function broadcastAs() {
        return 'bot.command';
    }

    public function broadcastWith() {
        return $this->payload;
    }

    public function tags() {
        return collect(['bot'])->concat($this->payload['tag'] ?? [])->all();
    }
}
