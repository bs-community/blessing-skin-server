<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CheckUserVerifiedTest extends TestCase
{
    use DatabaseTransactions;

    public function testHandle()
    {
        $unverified = User::factory()->create(['verified' => false]);

        option(['require_verification' => false]);
        $this->actingAs($unverified)
            ->get('/skinlib/upload')
            ->assertSuccessful();

        option(['require_verification' => true]);
        $this->actingAs($unverified)
            ->get('/skinlib/upload')
            ->assertStatus(403)
            ->assertSee(trans('auth.check.verified'));

        $this->actingAs(User::factory()->create())
            ->get('/skinlib/upload')
            ->assertSuccessful();

        $user = User::factory()->create(['verified' => false]);
        $this->actingAs($user)->get('/user/oauth/manage')->assertForbidden();
        $this->getJson('/oauth/clients')->assertForbidden();
        $user->verified = true;
        $user->save();
        $this->getJson('/oauth/clients')->assertSuccessful();
    }
}
