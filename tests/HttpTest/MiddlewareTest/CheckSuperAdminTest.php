<?php

namespace Tests;

use App\Models\User;

class CheckSuperAdminTest extends TestCase
{
    public function testHandle()
    {
        // Admin
        $this->actAs(factory(User::class, 'admin')->create())
            ->get('/admin/update')
            ->assertForbidden();

        // Super admin
        $this->actAs(factory(User::class, 'superAdmin')->create())
            ->get('/admin/update')
            ->assertSuccessful();
    }
}
