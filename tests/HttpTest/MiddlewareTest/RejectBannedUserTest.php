<?php

namespace Tests;

use App\Models\User;

class RejectBannedUserTest extends TestCase
{
    public function testHandle()
    {
        $user = factory(User::class)->states('banned')->make();
        $this->actingAs($user)->get('/user')->assertForbidden();
        $this->get('/user', ['accept' => 'application/json'])
            ->assertForbidden()
            ->assertJson(['code' => -1, 'message' => trans('auth.check.banned')]);
    }
}
