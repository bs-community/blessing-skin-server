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

        $this->getJson('/nope.json')
            ->assertStatus(404)
            ->assertSee(trans('general.unexistent-player'));

        $this->get('/skin/nope.png')
            ->assertStatus(404)
            ->assertSee(trans('general.unexistent-player'));

        option(['return_204_when_notfound' => true]);
        $this->getJson('/nope.json')->assertNoContent();

        $player = factory(Player::class)->create();
        $this->getJson("/{$player->name}.json")
            ->assertJson(['username' => $player->name]);  // Default is CSL API

        $this->getJson("/{$player->name}.json");
        Event::assertDispatched(\App\Events\CheckPlayerExists::class);

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
