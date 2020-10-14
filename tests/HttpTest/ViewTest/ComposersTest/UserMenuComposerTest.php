<?php

namespace Tests;

use App\Models\User;

class UserMenuComposerTest extends TestCase
{
    public function testAvatar()
    {
        $user = User::factory()->create(['avatar' => 5]);
        $this->actingAs($user)->get('/')->assertSee('/avatar/5?size=36');
        $this->get('/skinlib')->assertSee('/avatar/5?size=36');
        $this->get('/user')->assertSee('/avatar/5?size=36');
    }
}
