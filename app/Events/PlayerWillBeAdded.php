<?php

namespace App\Events;

class PlayerWillBeAdded extends Event
{
    public $playerName;

    public function __construct($playerName)
    {
        $this->playerName = $playerName;
    }
}
