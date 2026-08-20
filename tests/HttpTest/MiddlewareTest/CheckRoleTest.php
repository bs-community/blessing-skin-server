<?php

namespace Tests\HttpTest\MiddlewareTest;

use App\Models\User;
use Tests\TestCase;

class CheckRoleTest extends TestCase
{
    public function testHandle()
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin')
            ->assertForbidden();

        $this->actingAs(User::factory()->admin()->create())
            ->get('/admin')
            ->assertSuccessful();

        $this->get('/admin/update')->assertForbidden();

        $this->actingAs(User::factory()->superAdmin()->create())
            ->get('/admin/update')
            ->assertSuccessful();
    }
}
