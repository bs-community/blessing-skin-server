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
        $user = factory(User::class)->create();
        $player = factory(Player::class)->create(['uid' => $user->uid]);
        $this->assertEquals($player->name, $user->player_name);
    }

    public function testSetPlayerNameAttribute()
    {
        $user = factory(User::class)->create();
        $player = factory(Player::class)->create(['uid' => $user->uid]);
        $user->player_name = 'a';
        $player->refresh();
        $this->assertEquals('a', $player->name);
    }
}
