<?php

namespace Tests;

use Event;
use App\Models\User;
use App\Models\Player;
use App\Services\Facades\Option;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class MiddlewareTest extends TestCase
{
    use DatabaseTransactions;

    public function testAuthenticate()
    {
        $this->get('/user')->assertRedirect('auth/login');
        $this->actAs('normal')->assertAuthenticated();
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
            'user_closet', 'migrations', 'options', 'players', 'textures', 'users',
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
        Event::fake();

        $this->getJson('/nope.json')
            ->assertStatus(404)
            ->assertSee(trans('general.unexistent-player'));

        $this->get('/skin/nope.png')
            ->assertStatus(404)
            ->assertSee(trans('general.unexistent-player'));

        Option::set('return_204_when_notfound', true);
        $this->getJson('/nope.json')->assertStatus(204);

        $player = factory(\App\Models\Player::class)->create();
        $this->getJson("/{$player->name}.json")
            ->assertJson(['username' => $player->name]);  // Default is CSL API

        $this->getJson("/{$player->name}.json");
        Event::assertDispatched(\App\Events\CheckPlayerExists::class);

        $player = factory(\App\Models\Player::class)->create();
        $user = $player->user;
        $this->actingAs($user)
            ->postJson('/user/player/rename/-1', ['name' => 'name'])
            ->assertJson([
                'code' => 1,
                'message' => trans('general.unexistent-player'),
            ]);
    }

    public function testCheckPlayerOwner()
    {
        $other_user = factory(\App\Models\User::class)->create();
        $player = factory(\App\Models\Player::class)->create();
        $owner = $player->user;

        $this->actingAs($other_user)
            ->get('/user/player')
            ->assertSuccessful();

        $this->actingAs($other_user)
            ->postJson('/user/player/rename/'.$player->pid)
            ->assertJson([
                'code' => 1,
                'message' => trans('admin.players.no-permission'),
            ]);
    }

    public function testEnsureEmailFilled()
    {
        $noEmailUser = factory(User::class)->create(['email' => '']);
        $this->actingAs($noEmailUser)->get('/user')->assertRedirect('/auth/bind');

        $normalUser = factory(User::class)->create();
        $this->actingAs($normalUser)->get('/auth/bind')->assertRedirect('/user');
    }

    public function testFireUserAuthenticated()
    {
        Event::fake();
        $user = factory(User::class)->create();
        $this->actingAs($user)->get('/user');
        Event::assertDispatched(\App\Events\UserAuthenticated::class, function ($event) use ($user) {
            $this->assertEquals($user->uid, $event->user->uid);
            return true;
        });
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

    public function testRejectBannedUser()
    {
        $user = factory(User::class, 'banned')->create();
        $this->actingAs($user)->get('/user')->assertForbidden();
        $this->get('/user', ['accept' => 'application/json'])
            ->assertForbidden()
            ->assertJson(['code' => -1, 'message' => trans('auth.check.banned')]);
    }

    public function testRequireBindPlayer()
    {
        $user = factory(User::class)->create();
        $this->actingAs($user)->get('/user')->assertViewIs('user.index');
        $this->get('/user/player/bind')->assertRedirect('/user');

        option(['single_player' => true]);

        $this->getJson('/user/player/list')->assertHeader('content-type', 'application/json');

        $this->get('/user/player/bind')->assertViewIs('user.bind');
        $this->get('/user')->assertRedirect('/user/player/bind');

        factory(Player::class)->create(['uid' => $user->uid]);
        $this->get('/user')->assertViewIs('user.index');
        $this->get('/user/player/bind')->assertRedirect('/user');
    }

    public function testForbiddenIE()
    {
        $this->get('/', ['user-agent' => 'MSIE'])->assertSee(trans('errors.http.ie'));
        $this->get('/', ['user-agent' => 'Trident'])->assertSee(trans('errors.http.ie'));
    }
}
