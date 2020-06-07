<?php

namespace App\Listeners;

use App\Models\Player;
use App\Models\Texture;

class ResetPlayers
{
    public function handle(Texture $texture)
    {
        // no need to update players
        // if texture was switched from "private" to "public"
        if ($texture->exists && $texture->public) {
            return;
        }

        $type = $texture->type == 'cape' ? 'tid_cape' : 'tid_skin';
        $query = Player::where($type, $texture->tid);

        // texture was switched from "private" to "public"
        if ($texture->exists) {
            $query = $query->where('uid', '<>', $texture->uploader);
        }

        $query->update([$type => 0]);
    }
}
