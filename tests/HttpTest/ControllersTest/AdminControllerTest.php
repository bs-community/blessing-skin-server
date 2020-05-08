<?php

namespace Tests;

use App\Models\Player;
use App\Models\Texture;
use App\Models\User;
use App\Services\Plugin;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;

class AdminControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        // Do not use `WithoutMiddleware` trait
        parent::setUp();
        $this->actingAs(factory(\App\Models\User::class)->states('admin')->create());
    }

    public function testIndex()
    {
        $filter = Fakes\Filter::fake();

        $this->get('/admin')->assertSuccessful();
        $filter->assertApplied('grid:admin.index');
    }

    public function testChartData()
    {
        factory(User::class)->create();
        factory(User::class)->create(['register_at' => '2019-01-01 00:00:00']);
        factory(Texture::class)->create();
        $this->getJson('/admin/chart')
            ->assertJson(['labels' => [
                trans('admin.index.user-registration'),
                trans('admin.index.texture-uploads'),
            ]])
            ->assertJsonStructure(['labels', 'xAxis', 'data']);
    }

    public function testStatus()
    {
        $this->mock(\App\Services\PluginManager::class, function ($mock) {
            $mock->shouldReceive('getEnabledPlugins')
                ->andReturn(collect([
                    'a' => new Plugin('', ['title' => 'MyPlugin', 'version' => '0.0.0']),
                ]));
        });
        $filter = Fakes\Filter::fake();

        $this->get('/admin/status')
            ->assertSee(PHP_VERSION)
            ->assertSee('(1)')
            ->assertSee('MyPlugin')
            ->assertSee('0.0.0');
        $filter->assertApplied('grid:admin.status');
    }

    public function testUsers()
    {
        $this->get('/admin/users')->assertSee(trans('general.user-manage'));
    }

    public function testGetUserData()
    {
        $this->getJson('/admin/users/list')
            ->assertJsonStructure([
                'data' => [[
                    'uid',
                    'email',
                    'nickname',
                    'score',
                    'permission',
                    'register_at',
                    'operations',
                    'players_count',
                ]],
            ]);

        $user = factory(User::class)->create();
        $this->getJson('/admin/users/list?uid='.$user->uid)
            ->assertJson([
                'data' => [[
                    'uid' => $user->uid,
                    'email' => $user->email,
                    'nickname' => $user->nickname,
                    'score' => $user->score,
                    'permission' => $user->permission,
                    'players_count' => 0,
                ]],
            ]);
    }

    public function testPlayers()
    {
        $this->get('/admin/players')->assertSee(trans('general.player-manage'));
    }

    public function testGetPlayerData()
    {
        $player = factory(Player::class)->create();
        $user = $player->user;

        $this->getJson('/admin/players/list')
            ->assertJsonStructure([
                'data' => [[
                    'pid',
                    'uid',
                    'name',
                    'tid_skin',
                    'tid_cape',
                    'last_modified',
                ]],
            ]);

        $this->getJson('/admin/players/list?uid='.$user->uid)
            ->assertJson([
                'data' => [[
                    'pid' => $player->pid,
                    'uid' => $user->uid,
                    'name' => $player->name,
                    'tid_skin' => $player->tid_skin,
                    'tid_cape' => $player->tid_cape,
                ]],
            ]);
    }

    public function testUserAjaxHandler()
    {
        // Operate on an not-existed user
        $this->postJson('/admin/users')
            ->assertJson([
                'code' => 1,
                'message' => trans('admin.users.operations.non-existent'),
            ]);

        $user = factory(User::class)->create();

        // Operate without `action` field
        $this->postJson('/admin/users', ['uid' => $user->uid])
            ->assertJson([
                'code' => 1,
                'message' => trans('admin.users.operations.invalid'),
            ]);

        // An admin operating on a super admin should be forbidden
        $superAdmin = factory(User::class)->states('superAdmin')->create();
        $this->postJson('/admin/users', ['uid' => $superAdmin->uid])
            ->assertJson([
                'code' => 1,
                'message' => trans('admin.users.operations.no-permission'),
            ]);

        // Action is `email` but without `email` field
        $this->postJson(
            '/admin/users',
            ['uid' => $user->uid, 'action' => 'email']
        )->assertJsonValidationErrors(['email']);

        // Action is `email` but with an invalid email address
        $this->postJson(
            '/admin/users',
            ['uid' => $user->uid, 'action' => 'email', 'email' => 'invalid']
        )->assertJsonValidationErrors(['email']);

        // Using an existed email address
        $this->postJson(
            '/admin/users',
            ['uid' => $user->uid, 'action' => 'email', 'email' => $superAdmin->email]
        )->assertJson([
            'code' => 1,
            'message' => trans('admin.users.operations.email.existed', ['email' => $superAdmin->email]),
        ]);

        // Set email successfully
        $this->postJson(
            '/admin/users',
            ['uid' => $user->uid, 'action' => 'email', 'email' => 'a@b.c']
        )->assertJson([
            'code' => 0,
            'message' => trans('admin.users.operations.email.success'),
        ]);
        $this->assertDatabaseHas('users', [
            'uid' => $user->uid,
            'email' => 'a@b.c',
        ]);

        // Toggle verification
        $this->postJson(
            '/admin/users',
            ['uid' => $user->uid, 'action' => 'verification']
        )->assertJson([
            'code' => 0,
            'message' => trans('admin.users.operations.verification.success'),
        ]);
        $this->assertDatabaseHas('users', [
            'uid' => $user->uid,
            'verified' => 0,
        ]);

        // Action is `nickname` but without `nickname` field
        $this->postJson(
            '/admin/users',
            ['uid' => $user->uid, 'action' => 'nickname']
        )->assertJsonValidationErrors(['nickname']);

        // Set nickname successfully
        $this->postJson(
            '/admin/users',
            ['uid' => $user->uid, 'action' => 'nickname', 'nickname' => 'nickname']
        )->assertJson([
            'code' => 0,
            'message' => trans('admin.users.operations.nickname.success', ['new' => 'nickname']),
        ]);
        $this->assertDatabaseHas('users', [
            'uid' => $user->uid,
            'nickname' => 'nickname',
        ]);

        // Action is `password` but without `password` field
        $this->postJson(
            '/admin/users',
            ['uid' => $user->uid, 'action' => 'password']
        )->assertJsonValidationErrors(['password']);

        // Set a too short password
        $this->postJson(
            '/admin/users',
            ['uid' => $user->uid, 'action' => 'password', 'password' => '1']
        )->assertJsonValidationErrors(['password']);

        // Set a too long password
        $this->postJson(
            '/admin/users',
            ['uid' => $user->uid, 'action' => 'password', 'password' => Str::random(17)]
        )->assertJsonValidationErrors(['password']);

        // Set password successfully
        $this->postJson(
            '/admin/users',
            ['uid' => $user->uid, 'action' => 'password', 'password' => '12345678']
        )->assertJson([
            'code' => 0,
            'message' => trans('admin.users.operations.password.success'),
        ]);
        $user = User::find($user->uid);
        $this->assertTrue($user->verifyPassword('12345678'));

        // Action is `score` but without `score` field
        $this->postJson(
            '/admin/users',
            ['uid' => $user->uid, 'action' => 'score']
        )->assertJsonValidationErrors(['score']);

        // Action is `score` but with an not-an-integer value
        $this->postJson(
            '/admin/users',
            ['uid' => $user->uid, 'action' => 'score', 'score' => 'string']
        )->assertJsonValidationErrors(['score']);

        // Set score successfully
        $this->postJson(
            '/admin/users',
            ['uid' => $user->uid, 'action' => 'score', 'score' => 123]
        )->assertJson([
            'code' => 0,
            'message' => trans('admin.users.operations.score.success'),
        ]);
        $this->assertDatabaseHas('users', [
            'uid' => $user->uid,
            'score' => 123,
        ]);

        // Invalid permission value
        $this->postJson('/admin/users', [
            'uid' => $user->uid,
            'action' => 'permission',
            'permission' => -2,
        ])->assertJsonValidationErrors(['permission']);
        $user = User::find($user->uid);
        $this->assertEquals(User::NORMAL, $user->permission);

        // Update permission successfully
        $this->postJson('/admin/users', [
            'uid' => $user->uid,
            'action' => 'permission',
            'permission' => -1,
        ])->assertJson([
            'code' => 0,
            'message' => trans('admin.users.operations.permission'),
        ]);
        $user = User::find($user->uid);
        $this->assertEquals(User::BANNED, $user->permission);

        // Delete a user
        $this->postJson('/admin/users', ['uid' => $user->uid, 'action' => 'delete'])
            ->assertJson([
                'code' => 0,
                'message' => trans('admin.users.operations.delete.success'),
            ]);
        $this->assertNull(User::find($user->uid));
    }
}
