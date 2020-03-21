<?php

namespace App\Events;

use App\TerritoryWarPlan;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DMState implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $broadcastQueue = 'notifications';

    protected $plan;
    protected $member;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(TerritoryWarPlan $plan, $member)
    {
        $this->plan = $plan;
        $this->member = $member;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PresenceChannel('plan.' . $this->plan->id);
    }

    public function broadcastWith() {
        return ['member' => $this->member];
    }

    public function broadcastAs() {
        return 'member.dm.status';
    }

    public function tags() {
        return ['dm_status', 'member:' . $this->member['ally_code']];
    }
}
