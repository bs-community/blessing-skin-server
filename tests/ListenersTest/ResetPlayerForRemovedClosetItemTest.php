<?php

namespace Tests;

use App\Models\Player;
use App\Models\Texture;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ResetPlayerForRemovedClosetItemTest extends TestCase
{
    use DatabaseTransactions;

    public function testHandle()
    {
        $texture = factory(Texture::class)->create();
        $player = factory(Player::class)->create(['tid_skin' => $texture->tid]);

        event('closet.removed', [$texture, $player->user]);
        $player->refresh();
        $this->assertEquals(0, $player->tid_skin);
    }
}
