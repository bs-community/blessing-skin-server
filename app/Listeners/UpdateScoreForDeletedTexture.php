<?php

namespace App\Listeners;

class UpdateScoreForDeletedTexture
{
    public function handle($texture)
    {
        $uploader = $texture->owner;
        if ($uploader) {
            $ret = 0;
            if (option('return_score')) {
                $ret += $texture->size * (
                    $texture->public
                        ? (int) option('score_per_storage')
                        : (int) option('private_score_per_storage')
                );
            }

            if ($texture->public && option('take_back_scores_after_deletion', true)) {
                $ret -= (int) option('score_award_per_texture', 0);
            }

            $uploader->score += $ret;
            $uploader->save();
        }
    }
}
