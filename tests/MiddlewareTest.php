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
        $this->visit('/user')->seePageIs('/auth/login');

        // Normal user
        $this->actAs('normal')
            ->visit('/user')
            ->seePageIs('/user')
            ->assertResponseStatus(200);

        // Banned User
        $this->actAs('banned')
            ->get('/user')                   // Do not use `visit` method here.
            ->see('banned')
            ->dontSee('User Center')
            ->assertResponseStatus(403);

        // Binding email
        $noEmailUser = factory(App\Models\User::class)->create(['email' => '']);
        $this->withSession([
            'uid' => $noEmailUser->uid,
            'token' => $noEmailUser->getToken()
        ])->visit('/user')->see('Bind')->dontSee('User Center');

        $this->actAs($noEmailUser)
            ->get('/user?email=email')
            ->see('Bind');

        $other = factory(User::class)->create();
        $this->actAs($noEmailUser)
            ->get('/user?email='.$other->email)
            ->see(trans('auth.bind.registered'));

        $this->actAs($noEmailUser)
            ->get('/user?email=a@b.c')
            ->see('User Center');
        $this->assertEquals('a@b.c', User::find($noEmailUser->uid)->email);

        // Without token
        $this->withSession([
            'uid' => 0
        ])->visit('/user')->seePageIs('/auth/login');

        // Without invalid token
        $user = factory(User::class)->create();
        $this->withSession([
            'uid' => $user->uid,
            'token' => 'invalid'
        ])->visit('/user')->seePageIs('/auth/login');
    }

    public function testCheckAdministrator()
    {
        // Without logged in
        $this->get('/admin')->assertRedirectedTo('/auth/login');

        // Normal user
        $this->actAs('normal')
            ->get('/admin')
            ->assertResponseStatus(403);

        // Admin
        $this->actAs('admin')
            ->visit('/admin')
            ->seePageIs('/admin')
            ->assertResponseStatus(200);

        // Super admin
        $this->actAs('superAdmin')
            ->visit('/admin')
            ->seePageIs('/admin')
            ->assertResponseStatus(200);
    }

    public function testCheckInstallation()
    {
        $this->visit('/setup')->see('Already installed');

        $tables = [
            'closets', 'migrations', 'options', 'players', 'textures', 'users'
        ];
        array_walk($tables, function ($table) {
            Schema::dropIfExists($table);
        });
        $this->visit('/setup')->see(trans(
            'setup.wizard.welcome.text',
            ['version' => config('app.version')]
        ));
    }

    public function testCheckPlayerExist()
    {
        $this->get('/nope.json')
            ->assertResponseStatus(404)
            ->see('Un-existent player');

        $this->get('/skin/nope.png')
            ->assertResponseStatus(404)
            ->see('Un-existent player');

        Option::set('return_200_when_notfound', true);
        $this->get('/nope.json')
            ->assertResponseStatus(200)
            ->seeJson([
                'player_name' => 'nope',
                'errno' => 404,
                'msg' => 'Player Not Found.'
            ]);

        $player = factory(App\Models\Player::class)->create();
        $this->get("/{$player->player_name}.json")
            ->seeJson(['username' => $player->player_name]);  // Default is CSL API

        $this->expectsEvents(\App\Events\CheckPlayerExists::class);
        $this->get("/{$player->player_name}.json");

        $player = factory(\App\Models\Player::class)->create();
        $user = \App\Models\User::find($player->uid);
        $this->actAs($user)
            ->post('/user/player/rename', [
                'pid' => -1,
                'new_player_name' => 'name'
            ])->seeJson([
                'errno' => 1,
                'msg' => trans('general.unexistent-player')
            ]);
        $this->actAs($user)
            ->post('/user/player/rename', [
                'pid' => $player->pid,
                'new_player_name' => 'name'
            ])->seeJson([
                'errno' => 0
            ]);
    }

    public function testCheckPlayerOwner()
    {
        $other_user = factory(\App\Models\User::class)->create();
        $player = factory(\App\Models\Player::class)->create();
        $owner = \App\Models\User::find($player->uid);

        $this->actAs($other_user)
            ->visit('/user/player')
            ->assertResponseStatus(200);

        $this->actAs($other_user)
            ->post('/user/player/rename', [
                'pid' => $player->pid
            ])->seeJson([
                'errno' => 1,
                'msg' => trans('admin.players.no-permission')
            ]);

        $this->actAs($owner)
            ->post('/user/player/rename', [
                'pid' => $player->pid,
                'new_player_name' => 'name'
            ])->seeJson([
                'errno' => 0
            ]);
    }

    public function testRedirectIfAuthenticated()
    {
        $this->visit('/auth/login')
            ->seePageIs('/auth/login')
            ->dontSee('User Center');

        $user = factory(\App\Models\User::class)->create();

        $this->withSession(['uid' => $user->uid])
            ->visit('/auth/login')
            ->see('Invalid token');

        $this->withSession(['uid' => $user->uid, 'token' => 'nothing'])
            ->visit('/auth/login')
            ->seePageIs('/auth/login')
            ->see(trans('auth.check.token'));

        $this->actAs('normal')
            ->visit('/auth/login')
            ->seePageIs('/user');
    }

    public function testRedirectIfUrlEndsWithSlash()
    {
        $this->visit('/auth/login/')->seePageIs('/auth/login');
    }
}
