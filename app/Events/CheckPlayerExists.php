<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;

class CheckPlayerExists extends Event
{
    use SerializesModels;

    public $player_name;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($player_name)
    {
        $this->player_name = $player_name;
    }

}
