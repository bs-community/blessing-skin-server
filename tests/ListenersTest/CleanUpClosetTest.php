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
        $texture = Texture::factory()->create();
        $user = User::factory()->create(['score' => 0]);
        $user->closet()->attach($texture->tid, ['item_name' => '']);

        event('texture.privacy.updated', [$texture]);
        $user->refresh();
        $this->assertEquals($texture->likes, $texture->fresh()->likes);
        $this->assertEquals(0, $user->score);
    }

    public function testPrivateTexture()
    {
        option(['return_score' => true]);
        $uploader = User::factory()->create();
        $texture = Texture::factory()->create([
            'uploader' => $uploader->uid,
            'public' => false,
        ]);
        $uploader->closet()->attach($texture->tid, ['item_name' => '']);
        $user = User::factory()->create(['score' => 0]);
        $user->closet()->attach($texture->tid, ['item_name' => '']);

        $replicated = $texture->replicate();
        event('texture.privacy.updated', [$texture]);
        $uploader->refresh();
        $user->refresh();
        $this->assertEquals($replicated->likes - 1, $texture->fresh()->likes);
        $this->assertEquals((int) option('score_per_closet_item'), $user->score);
        $this->assertNull($user->closet()->find($texture->tid));
        $this->assertEquals(1, $uploader->closet()->count());
    }

    public function testDeletedTexture()
    {
        option(['return_score' => true]);
        $texture = Texture::factory()->create();
        $user = User::factory()->create(['score' => 0]);
        $user->closet()->attach($texture->tid, ['item_name' => '']);

        $texture->delete();
        event('texture.deleted', [$texture]);
        $user->refresh();
        $this->assertEquals((int) option('score_per_closet_item'), $user->score);
        $this->assertNull($user->closet()->find($texture->tid));
    }
}
