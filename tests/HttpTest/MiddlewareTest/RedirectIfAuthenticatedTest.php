<?php

namespace Tests;

use App\Models\User;

class RedirectIfAuthenticatedTest extends TestCase
{
    public function testHandle()
    {
        $this->get('/auth/login')
            ->assertViewIs('auth.login')
            ->assertDontSee(trans('general.user-center'));

        $this->actingAs(factory(User::class)->make())
            ->get('/auth/login')
            ->assertRedirect('/user');
    }
}
