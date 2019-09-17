<?php

namespace Tests;

use App\Models\User;

class AuthenticateTest extends TestCase
{
    public function testHandle()
    {
        $this->get('/user')->assertRedirect('auth/login');

        $user = factory(User::class)->make();
        $this->actingAs($user)->assertAuthenticated();
    }
}
