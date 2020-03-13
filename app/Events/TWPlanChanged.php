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

class TWPlanChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $broadcastQueue = 'notifications';

    protected $plan;
    protected $change;
    protected $zone;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(TerritoryWarPlan $plan, $zone, $change)
    {
        $this->plan = $plan;
        $this->change = $change;
        $this->zone = $zone;
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
        return ['zone' => $this->zone, 'change' => $this->change];
    }

    public function broadcastAs() {
        return 'plan.changed';
    }

    public function tags() {
        return ['change', 'plan:' . $this->plan->id];
    }
}
