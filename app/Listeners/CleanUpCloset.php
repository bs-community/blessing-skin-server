<?php

namespace App\Listeners;

use App\Models\Texture;
use App\Models\User;

class CleanUpCloset
{
    public function handle(Texture $texture)
    {
        // no need to update users' closet
        // if texture was switched from "private" to "public"
        if ($texture->exists && $texture->public) {
            return;
        }

        $likers = $texture->likers()->get();
        $likers->each(function (User $user) use ($texture) {
            $user->closet()->detach($texture->tid);
            if (option('return_score')) {
                $user->score += (int) option('score_per_closet_item');
                $user->save();
            }
        });

        if ($texture->exists) {
            $texture->decrement('likes', $likers->count());
        }
    }
}
