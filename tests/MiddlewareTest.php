<?php

namespace Tests;

use App\Models\User;
use App\Services\Facades\Option;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class MiddlewareTest extends TestCase
{
    use DatabaseTransactions;

    public function testCheckAuthenticated()
    {
        // Not logged in
        $this->get('/user')->assertRedirect('auth/login');
        $this->assertGuest();

        // Normal user
        $this->actAs('normal')
            ->assertAuthenticated();

        // Banned User
        $this->actAs('banned')
            ->get('/user')
            ->assertSee('banned')
            ->assertStatus(403);

        // Binding email
        $noEmailUser = factory(\App\Models\User::class)->create(['email' => '']);
        $this->actingAs($noEmailUser)
            ->get('/user')
            ->assertSee('Bind')
            ->assertDontSee('User Center');

        $this->actingAs($noEmailUser)
            ->get('/user?email=email')
            ->assertSee('Bind');

        $other = factory(User::class)->create();
        $this->actingAs($noEmailUser)
            ->get('/user?email='.$other->email)
            ->assertSee(trans('auth.bind.registered'));

        $this->actingAs($noEmailUser)
            ->get('/user?email=a@b.c')
            ->assertSee('User Center');
        $this->assertEquals('a@b.c', User::find($noEmailUser->uid)->email);
    }

    public function testCheckUserVerified()
    {
        $unverified = factory(User::class)->create(['verified' => false]);

        option(['require_verification' => false]);
        $this->actingAs($unverified)
            ->get('/skinlib/upload')
            ->assertSuccessful();

        option(['require_verification' => true]);
        $this->actingAs($unverified)
            ->get('/skinlib/upload')
            ->assertStatus(403)
            ->assertSee(trans('auth.check.verified'));

        $this->actAs('normal')
            ->get('/skinlib/upload')
            ->assertSuccessful();
    }

    public function testCheckAdministrator()
    {
        // Without logged in
        $this->get('/admin')->assertRedirect('/auth/login');

        // Normal user
        $this->actAs('normal')
            ->get('/admin')
            ->assertStatus(403);

        // Admin
        $this->actAs('admin')
            ->get('/admin')
            ->assertSuccessful();

        // Super admin
        $this->actAs('superAdmin')
            ->get('/admin')
            ->assertSuccessful();
    }

    public function testCheckSuperAdmin()
    {
        // Admin
        $this->actAs('admin')
            ->get('/admin/plugins/manage')
            ->assertForbidden();

        // Super admin
        $this->actAs('superAdmin')
            ->get('/admin/plugins/manage')
            ->assertSuccessful();
    }

    public function testCheckInstallation()
    {
        $this->get('/setup')->assertSee('Already installed');

        $tables = [
            'closets', 'migrations', 'options', 'players', 'textures', 'users'
        ];
        array_walk($tables, function ($table) {
            Schema::dropIfExists($table);
        });
        $this->get('/setup')->assertSee(trans(
            'setup.wizard.welcome.text',
            ['version' => config('app.version')]
        ));
    }

    public function testCheckPlayerExist()
    {
        $this->getJson('/nope.json')
            ->assertStatus(404)
            ->assertSee(trans('general.unexistent-player'));

        $this->get('/skin/nope.png')
            ->assertStatus(404)
            ->assertSee(trans('general.unexistent-player'));

        Option::set('return_204_when_notfound', true);
        $this->getJson('/nope.json')->assertStatus(204);

        $player = factory(\App\Models\Player::class)->create();
        $this->getJson("/{$player->player_name}.json")
            ->assertJson(['username' => $player->player_name]);  // Default is CSL API

        $this->expectsEvents(\App\Events\CheckPlayerExists::class);
        $this->getJson("/{$player->player_name}.json");

        $player = factory(\App\Models\Player::class)->create();
        $user = $player->user;
        $this->actAs($user)
            ->postJson('/user/player/rename', [
                'pid' => -1,
                'new_player_name' => 'name'
            ])->assertJson([
                'errno' => 1,
                'msg' => trans('general.unexistent-player')
            ]);
        $this->actAs($user)
            ->postJson('/user/player/rename', [
                'pid' => $player->pid,
                'new_player_name' => 'name'
            ])->assertJson([
                'errno' => 0
            ]);
    }

    public function testCheckPlayerOwner()
    {
        $other_user = factory(\App\Models\User::class)->create();
        $player = factory(\App\Models\Player::class)->create();
        $owner = $player->user;

        $this->actAs($other_user)
            ->get('/user/player')
            ->assertSuccessful();

        $this->actAs($other_user)
            ->postJson('/user/player/rename', [
                'pid' => $player->pid
            ])->assertJson([
                'errno' => 1,
                'msg' => trans('admin.players.no-permission')
            ]);

        $this->actAs($owner)
            ->postJson('/user/player/rename', [
                'pid' => $player->pid,
                'new_player_name' => 'name'
            ])->assertJson([
                'errno' => 0
            ]);
    }

    public function testRedirectIfAuthenticated()
    {
        $this->get('/auth/login')
            ->assertViewIs('auth.login')
            ->assertDontSee(trans('general.user-center'));

        $this->actingAs(factory(User::class)->create())
            ->get('/auth/login')
            ->assertRedirect('/user');
    }
}
