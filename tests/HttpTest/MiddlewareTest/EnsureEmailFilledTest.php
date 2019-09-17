<?php

namespace Tests;

use App\Models\User;

class EnsureEmailFilledTest extends TestCase
{
    public function testHandle()
    {
        $noEmailUser = factory(User::class)->make(['email' => '']);
        $this->actingAs($noEmailUser)->get('/user')->assertRedirect('/auth/bind');

        $normalUser = factory(User::class)->make();
        $this->actingAs($normalUser)->get('/auth/bind')->assertRedirect('/user');
    }
}
