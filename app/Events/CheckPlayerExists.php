<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class CheckPlayerExists extends Event
{
    use SerializesModels;

    public $player_name;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($player_name)
    {
        $this->player_name = $player_name;
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
