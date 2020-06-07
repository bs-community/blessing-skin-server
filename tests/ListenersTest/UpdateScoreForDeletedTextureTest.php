<?php

namespace Tests;

use App\Models\Texture;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UpdateScoreForDeletedTextureTest extends TestCase
{
    use DatabaseTransactions;

    public function testPublicTexture()
    {
        option(['return_score' => true]);
        $texture = factory(Texture::class)->create();
        $uploader = $texture->owner->replicate();

        event('texture.deleted', [$texture]);
        $this->assertEquals(
            $uploader->score + $texture->size * (int) option('score_per_storage'),
            $texture->owner->fresh()->score
        );
    }

    public function testPrivateTexture()
    {
        option(['return_score' => true]);
        $texture = factory(Texture::class)->create(['public' => false]);
        $uploader = $texture->owner->replicate();

        event('texture.deleted', [$texture]);
        $this->assertEquals(
            $uploader->score + $texture->size * (int) option('private_score_per_storage'),
            $texture->owner->fresh()->score
        );
    }

    public function testTakeBackAwardOfPublicTexture()
    {
        option([
            'score_award_per_texture' => 5,
            'take_back_scores_after_deletion' => true,
            'score_per_storage' => 0,
        ]);

        $texture = factory(Texture::class)->create();
        $uploader = $texture->owner->replicate();

        event('texture.deleted', [$texture]);
        $this->assertEquals(
            $uploader->score - 5,
            $texture->owner->fresh()->score
        );
    }

    public function testTakeBackAwardOfPrivateTexture()
    {
        option([
            'score_award_per_texture' => 5,
            'take_back_scores_after_deletion' => true,
            'private_score_per_storage' => 0,
        ]);

        $texture = factory(Texture::class)->create(['public' => false]);
        $uploader = $texture->owner->replicate();

        event('texture.deleted', [$texture]);
        $this->assertEquals(
            $uploader->score,
            $texture->owner->fresh()->score
        );
    }
}
