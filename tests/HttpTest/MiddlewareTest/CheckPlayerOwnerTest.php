<?php

namespace Tests;

use App\Models\Player;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CheckPlayerOwnerTest extends TestCase
{
    use DatabaseTransactions;

    public function testHandle()
    {
        $other_user = factory(User::class)->create();
        $player = factory(Player::class)->create();
        $owner = $player->user;

        $this->actingAs($other_user)
            ->get('/user/player')
            ->assertSuccessful();

        $this->actingAs($other_user)
            ->postJson('/user/player/rename/'.$player->pid)
            ->assertJson([
                'code' => 1,
                'message' => trans('admin.players.no-permission'),
            ]);
    }
}
