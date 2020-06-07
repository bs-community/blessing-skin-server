<?php

namespace App\Listeners;

use App\Models\Texture;
use App\Models\User;

class ResetPlayerForRemovedClosetItem
{
    public function handle(Texture $texture, User $user)
    {
        $type = $texture->type === 'cape' ? 'tid_cape' : 'tid_skin';

        $user->players()->where($type, $texture->tid)->update([$type => 0]);
    }
}
