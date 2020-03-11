<?php

namespace Tests;

use App\Models\User;

class CheckRole extends TestCase
{
    public function testHandle()
    {
        $this->actingAs(factory(User::class)->create())
            ->get('/admin')
            ->assertForbidden();

        $this->actingAs(factory(User::class)->states('admin')->create())
            ->get('/admin')
            ->assertSuccessful();

        $this->get('/admin/update')->assertForbidden();

        $this->actingAs(factory(User::class)->states('superAdmin')->create())
            ->get('/admin/update')
            ->assertSuccessful();
    }
}
