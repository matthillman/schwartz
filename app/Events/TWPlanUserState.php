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

class TWPlanUserState implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $broadcastQueue = 'notifications';

    protected $plan;
    protected $user;
    protected $zone;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(TerritoryWarPlan $plan, $user)
    {
        $this->plan = $plan;
        $this->user = $user;
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
        return ['user' => $this->user];
    }

    public function broadcastAs() {
        return 'user.changed';
    }

    public function tags() {
        return ['change', 'plan:' . $this->plan->id];
    }
}
