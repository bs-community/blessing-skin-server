<?php

namespace Tests;

use App\Models\User;

class UserMenuComposerTest extends TestCase
{
    public function testAvatar()
    {
        $user = factory(User::class)->create(['avatar' => 5]);
        $this->actingAs($user)->get('/')->assertSee(url('/avatar/5?size=36'));
        $this->get('/skinlib')->assertSee(url('/avatar/5?size=36'));
        $this->get('/user')->assertSee(url('/avatar/5?size=36'));
    }
}
