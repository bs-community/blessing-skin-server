<?php

namespace Tests;

use App\Models\Player;
use App\Models\Texture;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PlayerTest extends TestCase
{
    use DatabaseTransactions;

    public function testGetModelAttribute()
    {
        $player = Player::factory()->create();
        $this->assertEquals('default', $player->model);

        $alex = Texture::factory()->alex()->create();
        $player->tid_skin = $alex->tid;
        $player->save();
        $player->refresh();
        $this->assertEquals('slim', $player->model);

        $steve = Texture::factory()->create();
        $player->tid_skin = $steve->tid;
        $player->save();
        $player->refresh();
        $this->assertEquals('default', $player->model);
    }
}
