<?php

namespace Tests;

use App\Models\User;
use Event;

class UserPanelComposerTest extends TestCase
{
    public function testRenderUser()
    {
        $user = factory(User::class)->create();
        $this->actingAs($user);

        $this->get('/user')->assertSee(url('/avatar/user/'.$user->uid.'?size=45'));
    }

    public function testBadges()
    {
        $user = factory(User::class)->create();
        $this->actingAs($user);

        Event::listen(\App\Events\RenderingBadges::class, function ($event) {
            $event->badges[] = ['text' => 'Pro', 'color' => 'purple'];
        });

        $this->get('/user')
            ->assertSee('<span class="badge bg-purple mb-1 mr-2">Pro</span>', false);

        $user->permission = User::ADMIN;
        $user->save();
        $this->get('/user')
            ->assertSee('<span class="badge bg-primary mb-1 mr-2">STAFF</span>', false);
    }
}
