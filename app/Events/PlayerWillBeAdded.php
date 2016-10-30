<?php

namespace App\Events;

use App\Models\Player;

class PlayerWillBeAdded extends Event
{
    public $playerName;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($player_name)
    {
        $this->playerName = $player_name;
    }

}
