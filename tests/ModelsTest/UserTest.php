<?php

namespace Tests;

use App\Models\Player;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UserTest extends TestCase
{
    use DatabaseTransactions;

    public function testGetPlayerNameAttribute()
    {
        $user = User::factory()->create();
        $player = Player::factory()->create(['uid' => $user->uid]);
        $this->assertEquals($player->name, $user->player_name);
    }

    public function testSetPlayerNameAttribute()
    {
        $user = User::factory()->create();
        $player = Player::factory()->create(['uid' => $user->uid]);
        $user->player_name = 'a';
        $player->refresh();
        $this->assertEquals('a', $player->name);
    }
}
