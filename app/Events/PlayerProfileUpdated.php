<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class PlayerProfileUpdated extends Event
{
    use SerializesModels;

    public $player;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(\App\Models\Player $player)
    {
        $this->player = $player;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }
}
