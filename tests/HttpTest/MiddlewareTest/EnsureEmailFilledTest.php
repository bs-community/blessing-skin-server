<?php

namespace Tests\HttpTest\MiddlewareTest;

use App\Models\User;
use Tests\TestCase;

class EnsureEmailFilledTest extends TestCase
{
    public function testHandle()
    {
        $noEmailUser = User::factory()->make(['email' => '']);
        $this->actingAs($noEmailUser)->get('/user')->assertRedirect('/auth/bind');

        $normalUser = User::factory()->make();
        $this->actingAs($normalUser)->get('/auth/bind')->assertRedirect('/user');
    }
}
