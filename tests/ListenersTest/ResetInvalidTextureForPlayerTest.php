<?php

namespace Tests;

use App\Models\Player;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ResetInvalidTextureForPlayerTest extends TestCase
{
    use DatabaseTransactions;

    public function testHandle()
    {
        $pid = factory(Player::class)->create([
            'tid_skin' => 1,
            'tid_cape' => 2,
        ])->pid;

        $player = Player::find($pid);
        $this->assertEquals(0, $player->tid_skin);
        $this->assertEquals(0, $player->tid_cape);
    }
}
