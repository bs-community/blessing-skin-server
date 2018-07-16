<?php

use App\Models\User;
use App\Services\Facades\Option;
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

        // Normal user
        $this->actAs('normal')
            ->get('/user')
            ->assertViewIs('user.index')
            ->assertSuccessful();

        // Banned User
        $this->actAs('banned')
            ->get('/user')
            ->assertSee('banned')
            ->assertDontSee('User Center')
            ->assertStatus(403);

        // Binding email
        $noEmailUser = factory(App\Models\User::class)->create(['email' => '']);
        $this->withSession([
            'uid' => $noEmailUser->uid,
            'token' => $noEmailUser->getToken()
        ])->get('/user')->assertSee('Bind')->assertDontSee('User Center');

        $this->actAs($noEmailUser)
            ->get('/user?email=email')
            ->assertSee('Bind');

        $other = factory(User::class)->create();
        $this->actAs($noEmailUser)
            ->get('/user?email='.$other->email)
            ->assertSee(trans('auth.bind.registered'));

        $this->actAs($noEmailUser)
            ->get('/user?email=a@b.c')
            ->assertSee('User Center');
        $this->assertEquals('a@b.c', User::find($noEmailUser->uid)->email);

        // Without token
        $this->withSession([
            'uid' => 0
        ])->get('/user')->assertRedirect('/auth/login');

        // Without invalid token
        $user = factory(User::class)->create();
        $this->withSession([
            'uid' => $user->uid,
            'token' => 'invalid'
        ])->get('/user')->assertRedirect('/auth/login');
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
            ->assertSee('Un-existent player');

        $this->get('/skin/nope.png')
            ->assertStatus(404)
            ->assertSee('Un-existent player');

        Option::set('return_200_when_notfound', true);
        $this->getJson('/nope.json')
            ->assertSuccessful()
            ->assertJson([
                'player_name' => 'nope',
                'errno' => 404,
                'msg' => 'Player Not Found.'
            ]);

        $player = factory(App\Models\Player::class)->create();
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
            ->assertDontSee('User Center');

        $user = factory(\App\Models\User::class)->create();

        $this->withSession(['uid' => $user->uid])
            ->get('/auth/login')
            ->assertRedirect('/auth/login');

        $this->withSession(['uid' => $user->uid, 'token' => 'nothing'])
            ->get('/auth/login')
            ->assertRedirect('/auth/login');

        $this->actAs('normal')
            ->get('/auth/login')
            ->assertRedirect('/user');
    }
}
