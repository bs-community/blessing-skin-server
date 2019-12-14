<?php

namespace App\Events;

use App\Models\Player;

class PlayerWillBeDeleted extends Event
{
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
}
