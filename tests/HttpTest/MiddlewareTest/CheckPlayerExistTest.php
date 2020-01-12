<?php

namespace Tests;

use App\Models\Player;
use Event;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CheckPlayerExistTest extends TestCase
{
    use DatabaseTransactions;

    public function testHandle()
    {
        Event::fake();

        $this->getJson('/nope.json')->assertStatus(404);

        $player = factory(Player::class)->create();
        $this->getJson("/{$player->name}.json")
            ->assertJson(['username' => $player->name]);  // Default is CSL API

        $player = factory(Player::class)->create();
        $user = $player->user;
        $this->actingAs($user)
            ->postJson('/user/player/rename/-1', ['name' => 'name'])
            ->assertJson([
                'code' => 1,
                'message' => trans('general.unexistent-player'),
            ]);
    }
}
