<?php

namespace App\Listeners\SinglePlayer;

use App\Models\Player;

class UpdateOwnerNickName
{
    /**
     * @param Player $player
     */
    public function handle($player)
    {
        $owner = $player->user;

        if (option('single_player', false) && $owner) {
            $owner->nickname = $player->name;
            $owner->save();
        }
    }
}
