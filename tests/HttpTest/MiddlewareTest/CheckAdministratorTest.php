<?php

namespace Tests;

use App\Models\User;

class CheckAdministratorTest extends TestCase
{
    public function testHandle()
    {
        // Without logged in
        $this->get('/admin')->assertRedirect('/auth/login');

        // Normal user
        $this->actingAs(factory(User::class)->make())
            ->get('/admin')
            ->assertStatus(403);

        // Admin
        $this->actingAs(factory(User::class, 'admin')->make())
            ->get('/admin')
            ->assertSuccessful();

        // Super admin
        $this->actingAs(factory(User::class, 'superAdmin')->make())
            ->get('/admin')
            ->assertSuccessful();
    }
}
