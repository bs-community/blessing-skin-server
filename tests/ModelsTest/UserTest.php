<?php

namespace Tests;

use App\Models\User;
use App\Models\Player;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UserTest extends TestCase
{
    use DatabaseTransactions;

    public function testSign()
    {
        $user = factory(User::class)->make([
            'last_sign_at' => get_datetime_string(time()),
        ]);
        $user->sign();
        $this->assertFalse($user->sign());
    }

    public function testGetNickName()
    {
        $user = new User();
        $this->assertEquals(
            trans('general.unexistent-user'),
            $user->getNickName()
        );
    }

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
