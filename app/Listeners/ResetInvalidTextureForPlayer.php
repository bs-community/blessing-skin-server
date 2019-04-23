<?php

namespace App\Listeners;

class ResetInvalidTextureForPlayer
{
    public function handle(\App\Events\PlayerRetrieved $event)
    {
        $player = $event->player;

        foreach (['skin', 'cape'] as $type) {
            $field = "tid_$type";
            if (! \App\Models\Texture::find($player->$field)) {
                $player->$field = 0;
            }
        }
        $player->save();
    }
}
