<?php

namespace App\Events;

class PlayerWasDeleted extends Event
{
    public $playerName;

    public function __construct($playerName)
    {
        $this->playerName = $playerName;
    }
}
