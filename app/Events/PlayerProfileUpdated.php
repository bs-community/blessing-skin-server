<?php

namespace App\Events;

use App\Models\Player;

class PlayerProfileUpdated extends Event
{
    public $player;

    /**
     * Create a new event instance.
     *
     * @param  Player $player
     * @return void
     */
    public function __construct(Player $player)
    {
        $this->player = $player;
    }
}
