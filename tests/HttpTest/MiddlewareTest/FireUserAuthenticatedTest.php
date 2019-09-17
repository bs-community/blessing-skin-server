<?php

namespace Tests;

use Event;
use App\Models\User;

class FireUserAuthenticatedTest extends TestCase
{
    public function testHandle()
    {
        Event::fake();
        $user = factory(User::class)->make();
        $this->actingAs($user)->get('/user');
        Event::assertDispatched(\App\Events\UserAuthenticated::class, function ($event) use ($user) {
            $this->assertEquals($user->uid, $event->user->uid);

            return true;
        });
    }
}
