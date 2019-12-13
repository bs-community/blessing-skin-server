<?php

namespace Tests;

use Event;
use App\Models\User;

class UserPanelComposerTest extends TestCase
{
    public function testRenderUser()
    {
        $user = factory(User::class)->make();
        $this->actingAs($user);

        $this->get('/user')
            ->assertSee(
                url('avatar/45/'.base64_encode($user->email).'.png?tid='.$user->avatar)
            );
    }

    public function testBadges()
    {
        $user = factory(User::class)->make();
        $this->actingAs($user);

        Event::listen(\App\Events\RenderingBadges::class, function ($event) {
            $event->badges[] = ['text' => 'Pro', 'color' => 'purple'];
        });

        $this->get('/user')
            ->assertSee('<span class="badge bg-purple mb-1 mr-2">Pro</span>');

        $user->permission = User::ADMIN;
        $user->save();
        $this->get('/user')
            ->assertSee('<span class="badge bg-primary mb-1">STAFF</span>');
    }
}
