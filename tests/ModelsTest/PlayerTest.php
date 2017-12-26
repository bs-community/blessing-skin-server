<?php

use App\Models\Player;
use App\Models\Texture;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PlayerTest extends TestCase
{
    use DatabaseTransactions;

    public function testGetTexture()
    {
        $steve = factory(Texture::class)->create();
        $alex = factory(Texture::class, 'alex')->create();
        $player = factory(Player::class)->create([
            'tid_steve' => $steve->tid,
            'tid_alex' => $alex->tid
        ]);

        $player = Player::find($player->pid);
        $this->assertEquals($steve->hash, $player->getTexture('skin'));

        $player->setPreference('slim');
        $player = Player::find($player->pid);
        $this->assertEquals($alex->hash, $player->getTexture('skin'));

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
}
