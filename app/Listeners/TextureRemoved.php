<?php

namespace App\Listeners;

class TextureRemoved
{
    public function handle(\App\Events\TextureDeleting $event)
    {
        $texture = $event->texture;

        $texture->likers()->get()->each(function ($user) use ($texture) {
            $user->closet()->detach($texture->tid);
            if (option('return_score')) {
                $user->score += option('score_per_closet_item');
                $user->save();
            }
        });

        if ($uploader = \App\Models\User::find($texture->uploader)) {
            $ret = 0;
            if (option('return_score')) {
                $ret += $texture->size * (
                    $texture->public
                        ? option('score_per_storage')
                        : option('private_score_per_storage')
                );
            }

            if ($texture->public && option('take_back_scores_after_deletion', true)) {
                $ret -= option('score_award_per_texture', 0);
            }

            $uploader->score += $ret;
            $uploader->save();
        }
    }
}
