<?php

namespace App\Events;

use App\Models\Player;

class GetPlayerJson extends Event
{
    public $player;

    /**
     * CSL_API = 0
     * USM_API = 1.
     *
     * @var int
     */
    public $apiType;

    /**
     * Create a new event instance.
     *
     * @param int $apiType
     *
     * @return void
     */
    public function __construct(Player $player, $apiType)
    {
        $this->player = $player;
        $this->apiType = $apiType;
    }
}
