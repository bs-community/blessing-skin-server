<?php

namespace App\Events;

use App\Models\Player;
use Illuminate\Queue\SerializesModels;

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

}
