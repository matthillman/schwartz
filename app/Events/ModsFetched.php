<?php

namespace App\Events;

use App\ModUser;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ModsFetched implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $user;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(ModUser $user) {
        $this->user = $user;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn() {
        return new PrivateChannel('mods.'.$this->user->name);
    }

    public function broadcastAs() {
        return 'mods.fetched';
    }

    public function broadcastWith() {
        return ['mods' => $this->user->name];
    }

    public function tags() {
        return ['fetched', 'mods:' . $this->user->name];
    }
}
