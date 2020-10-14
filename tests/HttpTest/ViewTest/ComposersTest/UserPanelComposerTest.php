<?php

namespace Tests;

use App\Models\User;
use Event;

class UserPanelComposerTest extends TestCase
{
    public function testRenderUser()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get('/user')->assertSee('/avatar/0?size=45');
    }

    public function testBadges()
    {
        $filter = Fakes\Filter::fake();

        $user = User::factory()->create();
        $this->actingAs($user);

        Event::listen(\App\Events\RenderingBadges::class, function ($event) {
            $event->badges[] = ['text' => 'Pro', 'color' => 'purple'];
        });

        $this->get('/user')
            ->assertSee('<span class="badge bg-purple mb-1 mr-2">Pro</span>', false);
        $filter->assertApplied('user_badges', function ($badges, $user) {
            $this->assertCount(1, $badges);
            $this->assertInstanceOf(User::class, $user);

            return true;
        });

        $user->permission = User::ADMIN;
        $user->save();
        $this->get('/user')
            ->assertSee('<span class="badge bg-primary mb-1 mr-2">STAFF</span>', false);
    }
}
