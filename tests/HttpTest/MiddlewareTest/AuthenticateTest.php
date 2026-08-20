<?php

namespace Tests\HttpTest\MiddlewareTest;

use App\Models\User;
use Tests\TestCase;

class AuthenticateTest extends TestCase
{
    public function testHandle()
    {
        $this->get('/user')->assertRedirect('auth/login');

        $user = User::factory()->make();
        $this->actingAs($user)->assertAuthenticated();
    }
}
