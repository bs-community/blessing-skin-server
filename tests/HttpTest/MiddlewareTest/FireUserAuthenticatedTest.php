<?php

namespace Tests\HttpTest\MiddlewareTest;

use App\Models\User;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

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
