<?php

namespace Tests;

use App\Models\User;
use App\Models\Closet;
use App\Models\Texture;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ClosetTest extends TestCase
{
    use DatabaseTransactions;

    public function testAll()
    {
        for ($i = 0; $i < 2; $i++) {
            $user = factory(User::class)->create();
            (new Closet($user->uid))->save();
        }
        $this->assertCount(2, Closet::all());
    }

    public function testFilterInvalidTexture()
    {
        $other = factory(User::class)->create();
        $texture = factory(Texture::class)->create([
            'uploader' => $other->uid,
            'public' => false,
        ]);
        $user = factory(User::class)->create();
        $closet = new Closet($user->uid);
        $closet->add(-1, '');
        $closet->add($texture->tid, '');
        $closet->save();

        $this->assertCount(0, (new Closet($user->uid))->getItems());
        $this->assertEquals(
            $user->score + 2 * option('score_per_closet_item'),
            User::find($user->uid)->score
        );

        option(['return_score' => false]);
        $closet = new Closet($user->uid);
        $closet->add(-1, '');
        $closet->add($texture->tid, '');
        $closet->save();
        $user = User::find($user->uid);
        $this->assertCount(0, (new Closet($user->uid))->getItems());
        $this->assertEquals($user->score, User::find($user->uid)->score);
    }
}
