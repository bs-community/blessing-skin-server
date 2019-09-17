<?php

namespace Tests;

use App\Models\User;
use App\Models\Player;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class RequireBindPlayerTest extends TestCase
{
    use DatabaseTransactions;

    public function testHandle()
    {
        $user = factory(User::class)->create();
        $this->actingAs($user)->get('/user')->assertViewIs('user.index');
        $this->get('/user/player/bind')->assertRedirect('/user');

        option(['single_player' => true]);

        $this->getJson('/user/player/list')->assertHeader('content-type', 'application/json');

        $this->get('/user/player/bind')->assertViewIs('user.bind');
        $this->get('/user')->assertRedirect('/user/player/bind');

        factory(Player::class)->create(['uid' => $user->uid]);
        $this->get('/user')->assertViewIs('user.index');
        $this->get('/user/player/bind')->assertRedirect('/user');
    }
}
