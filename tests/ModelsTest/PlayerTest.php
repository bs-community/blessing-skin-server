<?php

namespace Tests;

use App\Models\Player;
use App\Models\Texture;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PlayerTest extends TestCase
{
    use DatabaseTransactions;

    public function testGetTexture()
    {
        $skin = factory(Texture::class)->create();
        $player = factory(Player::class)->create(['tid_skin' => $skin->tid]);

        $player = Player::find($player->pid);
        $this->assertEquals($skin->hash, $player->getTexture('skin'));

        $this->assertFalse($player->getTexture('invalid_type'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetJsonProfile()
    {
        $player = factory(Player::class)->make();
        $this->assertNull($player->getJsonProfile(-1));
    }

    public function testGetPlayerName()
    {
        $player = factory(Player::class)->make();
        $this->assertEquals($player->name, $player->player_name);
    }
}
