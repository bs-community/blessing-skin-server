<?php

namespace Tests;

use App\Models\User;

class CheckSuperAdminTest extends TestCase
{
    public function testHandle()
    {
        // Admin
        $this->actAs(factory(User::class, 'admin')->make())
            ->get('/admin/plugins/manage')
            ->assertForbidden();

        // Super admin
        $this->actAs(factory(User::class, 'superAdmin')->make())
            ->get('/admin/plugins/manage')
            ->assertSuccessful();
    }
}
