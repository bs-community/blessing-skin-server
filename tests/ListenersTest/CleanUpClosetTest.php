<?php

namespace Tests;

use App\Models\Texture;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CleanUpClosetTest extends TestCase
{
    use DatabaseTransactions;

    public function testPublicTexture()
    {
        option(['return_score' => true]);
        $texture = factory(Texture::class)->create();
        $user = factory(User::class)->create(['score' => 0]);
        $user->closet()->attach($texture->tid, ['item_name' => '']);

        event('texture.privacy.updated', [$texture]);
        $user->refresh();
        $this->assertEquals($texture->likes, $texture->fresh()->likes);
        $this->assertEquals(0, $user->score);
    }

    public function testPrivateTexture()
    {
        option(['return_score' => true]);
        $texture = factory(Texture::class)->create(['public' => false]);
        $user = factory(User::class)->create(['score' => 0]);
        $user->closet()->attach($texture->tid, ['item_name' => '']);

        $replicated = $texture->replicate();
        event('texture.privacy.updated', [$texture]);
        $user->refresh();
        $this->assertEquals($replicated->likes - 1, $texture->fresh()->likes);
        $this->assertEquals((int) option('score_per_closet_item'), $user->score);
        $this->assertNull($user->closet()->find($texture->tid));
    }

    public function testDeletedTexture()
    {
        option(['return_score' => true]);
        $texture = factory(Texture::class)->create();
        $user = factory(User::class)->create(['score' => 0]);
        $user->closet()->attach($texture->tid, ['item_name' => '']);

        $texture->delete();
        event('texture.deleted', [$texture]);
        $user->refresh();
        $this->assertEquals((int) option('score_per_closet_item'), $user->score);
        $this->assertNull($user->closet()->find($texture->tid));
    }
}
