<?php

namespace Tests;

use App\Models\User;
use Event;

class FireUserAuthenticatedTest extends TestCase
{
    public function testHandle()
    {
        Event::fake();
        $user = User::factory()->make();
        $this->actingAs($user)->get('/user');
        Event::assertDispatched(\App\Events\UserAuthenticated::class, function ($event) use ($user) {
            $this->assertEquals($user->uid, $event->user->uid);

            return true;
        });
    }
}
