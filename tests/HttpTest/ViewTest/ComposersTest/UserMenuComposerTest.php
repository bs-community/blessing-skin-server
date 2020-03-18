<?php

namespace Tests;

use App\Models\User;

class UserMenuComposerTest extends TestCase
{
    public function testAvatar()
    {
        $user = factory(User::class)->create();
        $this->actingAs($user)
            ->get('/')
            ->assertSee(url('/avatar/user/'.$user->uid.'?size=36'));
        $this->actingAs($user)
            ->get('/skinlib')
            ->assertSee(url('/avatar/user/'.$user->uid.'?size=36'));
        $this->actingAs($user)
            ->get('/user')
            ->assertSee(url('/avatar/user/'.$user->uid.'?size=36'));
    }
}
