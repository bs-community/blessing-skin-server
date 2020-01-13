<?php

namespace Tests;

use App\Models\User;

class CheckRole extends TestCase
{
    public function testHandle()
    {
        $this->actAs(factory(User::class)->create())
            ->get('/admin')
            ->assertForbidden();

        $this->actAs(factory(User::class, 'admin')->create())
            ->get('/admin')
            ->assertSuccessful();

        $this->get('/admin/update')->assertForbidden();

        $this->actAs(factory(User::class, 'superAdmin')->create())
            ->get('/admin/update')
            ->assertSuccessful();
    }
}
