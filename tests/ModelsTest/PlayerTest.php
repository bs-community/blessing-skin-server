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

    public function testUpdateLastModified()
    {
        $player = factory(Player::class)->make();
        $this->expectsEvents(\App\Events\PlayerProfileUpdated::class);
        $player->updateLastModified();
    }

    public function testGetTidSkinAttribute()
    {
        $player = factory(Player::class)->create([
            'tid_skin' => -1,
            'preference' => 'default',
            'tid_steve' => 5,
        ]);
        $this->assertEquals(5, $player->tid_skin);
        $this->assertEquals(5, Player::find($player->pid)->tid_skin);

        $player = factory(Player::class)->create([
            'tid_skin' => -1,
            'preference' => 'slim',
            'tid_alex' => 6,
        ]);
        $this->assertEquals(6, $player->tid_skin);
        $this->assertEquals(6, Player::find($player->pid)->tid_skin);
    }
}
