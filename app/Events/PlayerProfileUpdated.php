<?php

namespace App\Events;

use App\Models\Player;
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
    public function __construct(Player $player)
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
