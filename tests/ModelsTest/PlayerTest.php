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

    public function testGetModelAttribute()
    {
        $player = factory(Player::class)->create();
        $this->assertEquals('default', $player->model);

        $alex = factory(Texture::class, 'alex')->create();
        $player->tid_skin = $alex->tid;
        $player->save();
        $player->refresh();
        $this->assertEquals('slim', $player->model);

        $steve = factory(Texture::class)->create();
        $player->tid_skin = $steve->tid;
        $player->save();
        $player->refresh();
        $this->assertEquals('default', $player->model);
    }
}
