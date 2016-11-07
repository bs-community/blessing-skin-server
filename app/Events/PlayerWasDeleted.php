<?php

namespace App\Events;

class PlayerWasDeleted extends Event
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
