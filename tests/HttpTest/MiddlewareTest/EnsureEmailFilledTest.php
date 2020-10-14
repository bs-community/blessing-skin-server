<?php

namespace Tests;

use App\Models\User;

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
