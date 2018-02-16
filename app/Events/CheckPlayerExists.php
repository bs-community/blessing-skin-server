<?php

namespace App\Events;

class CheckPlayerExists extends Event
{
    public $playerName;

    /**
     * Create a new event instance.
     *
     * @param  string $playerName
     * @return void
     */
    public function __construct($playerName)
    {
        $this->playerName = $playerName;
    }
}
