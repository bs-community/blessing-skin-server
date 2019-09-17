<?php

namespace Tests;

use App\Models\User;

class UserMenuComposerTest extends TestCase
{
    public function testAvatar()
    {
        $user = factory(User::class)->make();
        $this->actingAs($user)
            ->get('/user')
            ->assertSee(
                url('avatar/128/'.base64_encode($user->email).'.png?tid='.$user->avatar)
            );
    }

    public function testTinyAvatar()
    {
        $user = factory(User::class)->make();
        $this->actingAs($user)
            ->get('/')
            ->assertSee(
                url('avatar/25/'.base64_encode($user->email).'.png?tid='.$user->avatar)
            );
        $this->actingAs($user)
            ->get('/skinlib')
            ->assertSee(
                url('avatar/25/'.base64_encode($user->email).'.png?tid='.$user->avatar)
            );
        $this->actingAs($user)
            ->get('/user')
            ->assertDontSee(
                url('avatar/25/'.base64_encode($user->email).'.png?tid='.$user->avatar)
            );
    }
}
