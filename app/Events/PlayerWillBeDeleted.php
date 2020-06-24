<?php

namespace App\Events;

use App\Models\Player;

class PlayerWillBeDeleted extends Event
{
    public $player;

    public function __construct(Player $player)
    {
        $this->player = $player;
    }
}
