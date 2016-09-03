<?php

namespace App\Events;

use App\Models\Player;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class GetPlayerJson extends Event
{
    use SerializesModels;

    public $player;

    public $api_type;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Player $player, $api_type)
    {
        $this->player   = $player;
        $this->api_type = $api_type;
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
