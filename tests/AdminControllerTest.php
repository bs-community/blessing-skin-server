<?php

namespace Tests;

use Notification;
use App\Models\User;
use App\Models\Player;
use App\Notifications;
use App\Models\Texture;
use App\Services\Plugin;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AdminControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        // Do not use `WithoutMiddleware` trait
        parent::setUp();
        $this->actAs('admin');
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

    public function testSendNotification()
    {
        $admin = factory(User::class, 'admin')->create();
        $normal = factory(User::class)->create();
        Notification::fake();

        $this->actingAs($admin)
            ->post('/admin/notifications/send', [
                'receiver' => 'all',
                'title' => 'all users',
                'content' => null,
            ])
            ->assertRedirect('/admin')
            ->assertSessionHas('sentResult', trans('admin.notifications.send.success'));
        Notification::assertSentTo(
            [$admin, $normal],
            Notifications\SiteMessage::class,
            function ($notification) {
                $this->assertEquals('all users', $notification->title);

                return true;
            }
        );

        Notification::fake();
        Notification::assertNothingSent();
        $this->post('/admin/notifications/send', [
            'receiver' => 'normal',
            'title' => 'normal only',
            'content' => 'hi',
        ]);
        Notification::assertSentTo(
            $normal,
            Notifications\SiteMessage::class,
            function ($notification) {
                $this->assertEquals('normal only', $notification->title);
                $this->assertEquals('hi', $notification->content);

                return true;
            }
        );
        Notification::assertNotSentTo($admin, Notifications\SiteMessage::class);

        Notification::fake();
        Notification::assertNothingSent();
        $this->post('/admin/notifications/send', [
            'receiver' => 'uid',
            'title' => 'uid',
            'content' => null,
            'uid' => $normal->uid,
        ]);
        Notification::assertSentTo(
            $normal,
            Notifications\SiteMessage::class,
            function ($notification) {
                $this->assertEquals('uid', $notification->title);

                return true;
            }
        );
        Notification::assertNotSentTo($admin, Notifications\SiteMessage::class);

        Notification::fake();
        Notification::assertNothingSent();
        $this->post('/admin/notifications/send', [
            'receiver' => 'email',
            'title' => 'email',
            'content' => null,
            'email' => $normal->email,
        ]);
        Notification::assertSentTo(
            $normal,
            Notifications\SiteMessage::class,
            function ($notification) {
                $this->assertEquals('email', $notification->title);

                return true;
            }
        );
        Notification::assertNotSentTo($admin, Notifications\SiteMessage::class);
    }

    public function testStatus()
    {
        $this->mock(\App\Services\PluginManager::class, function ($mock) {
            $mock->shouldReceive('getEnabledPlugins')
                ->andReturn(collect([
                    'a' => new Plugin('', ['title' => 'MyPlugin', 'version' => '0.0.0']),
                ]));
        });

        $this->get('/admin/status')
            ->assertSee(PHP_VERSION)
            ->assertSee(humanize_db_type())
            ->assertSee('(1)')
            ->assertSee('MyPlugin')
            ->assertSee('0.0.0');
    }

    public function testUsers()
    {
        $this->get('/admin/users')->assertSee(trans('general.user-manage'));
    }

    public function testGetUserData()
    {
        $this->getJson('/admin/user-data')
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
        $this->getJson('/admin/user-data?uid='.$user->uid)
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

        $this->getJson('/admin/player-data')
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

        $this->getJson('/admin/player-data?uid='.$user->uid)
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
        $superAdmin = factory(User::class, 'superAdmin')->create();
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

        // Action is `nickname` but with an invalid nickname
        $this->postJson(
            '/admin/users',
            ['uid' => $user->uid, 'action' => 'nickname', 'nickname' => '\\']
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

    public function testPlayerAjaxHandler()
    {
        $player = factory(Player::class)->create();

        // Operate on a not-existed player
        $this->postJson('/admin/players', ['pid' => -1])
            ->assertJson([
                'code' => 1,
                'message' => trans('general.unexistent-player'),
            ]);

        // An admin cannot operate another admin's player
        $admin = factory(User::class, 'admin')->create();
        $this->postJson(
            '/admin/players',
            ['pid' => factory(Player::class)->create(['uid' => $admin->uid])->pid]
        )->assertJson([
            'code' => 1,
            'message' => trans('admin.players.no-permission'),
        ]);
        $superAdmin = factory(User::class, 'superAdmin')->create();
        $this->postJson(
            '/admin/players',
            ['pid' => factory(Player::class)->create(['uid' => $superAdmin->uid])->pid]
        )->assertJson([
            'code' => 1,
            'message' => trans('admin.players.no-permission'),
        ]);
        // For self is OK
        $this->actingAs($admin)->postJson(
            '/admin/players',
            ['pid' => factory(Player::class)->create(['uid' => $admin->uid])->pid]
        )->assertJson([
            'code' => 1,
            'message' => trans('admin.users.operations.invalid'),
        ]);

        // Change texture without `type` field
        $this->postJson('/admin/players', [
            'pid' => $player->pid,
            'action' => 'texture',
        ])->assertJsonValidationErrors(['type']);

        // Change texture without `tid` field
        $this->postJson('/admin/players', [
            'pid' => $player->pid,
            'action' => 'texture',
            'type' => 'skin',
        ])->assertJsonValidationErrors(['tid']);

        // Change texture with a not-integer value
        $this->postJson('/admin/players', [
            'pid' => $player->pid,
            'action' => 'texture',
            'type' => 'skin',
            'tid' => 'string',
        ])->assertJsonValidationErrors(['tid']);

        // Invalid texture
        $this->postJson('/admin/players', [
            'pid' => $player->pid,
            'action' => 'texture',
            'type' => 'skin',
            'tid' => -1,
        ])->assertJson([
            'code' => 1,
            'message' => trans('admin.players.textures.non-existent', ['tid' => -1]),
        ]);

        $skin = factory(Texture::class)->create();
        $cape = factory(Texture::class, 'cape')->create();

        // Skin
        $this->postJson('/admin/players', [
            'pid' => $player->pid,
            'action' => 'texture',
            'type' => 'skin',
            'tid' => $skin->tid,
        ])->assertJson([
            'code' => 0,
            'message' => trans('admin.players.textures.success', ['player' => $player->name]),
        ]);
        $player = Player::find($player->pid);
        $this->assertEquals($skin->tid, $player->tid_skin);

        // Cape
        $this->postJson('/admin/players', [
            'pid' => $player->pid,
            'action' => 'texture',
            'type' => 'cape',
            'tid' => $cape->tid,
        ])->assertJson([
            'code' => 0,
            'message' => trans('admin.players.textures.success', ['player' => $player->name]),
        ]);
        $player = Player::find($player->pid);
        $this->assertEquals($cape->tid, $player->tid_cape);

        // Reset texture
        $this->postJson('/admin/players', [
            'pid' => $player->pid,
            'action' => 'texture',
            'type' => 'skin',
            'tid' => 0,
        ])->assertJson([
            'code' => 0,
            'message' => trans('admin.players.textures.success', ['player' => $player->name]),
        ]);
        $player = Player::find($player->pid);
        $this->assertEquals(0, $player->tid_skin);
        $this->assertNotEquals(0, $player->tid_cape);

        $this->postJson('/admin/players', [
            'pid' => $player->pid,
            'action' => 'texture',
            'type' => 'cape',
            'tid' => 0,
        ])->assertJson([
            'code' => 0,
            'message' => trans('admin.players.textures.success', ['player' => $player->name]),
        ]);
        $player = Player::find($player->pid);
        $this->assertEquals(0, $player->tid_cape);

        // Change owner without `uid` field
        $this->postJson('/admin/players', [
            'pid' => $player->pid,
            'action' => 'owner',
        ])->assertJsonValidationErrors(['uid']);

        // Change owner with a not-integer `uid` value
        $this->postJson('/admin/players', [
            'pid' => $player->pid,
            'action' => 'owner',
            'uid' => 'string',
        ])->assertJsonValidationErrors(['uid']);

        // Change owner to a not-existed user
        $this->postJson('/admin/players', [
            'pid' => $player->pid,
            'action' => 'owner',
            'uid' => -1,
        ])->assertJson([
            'code' => 1,
            'message' => trans('admin.users.operations.non-existent'),
        ]);

        // Change owner successfully
        $user = factory(User::class)->create();
        $this->postJson('/admin/players', [
            'pid' => $player->pid,
            'action' => 'owner',
            'uid' => $user->uid,
        ])->assertJson([
            'code' => 0,
            'message' => trans(
                'admin.players.owner.success',
                ['player' => $player->name, 'user' => $user->nickname]
            ),
        ]);

        // Rename a player without `name` field
        $this->postJson('/admin/players', [
            'pid' => $player->pid,
            'action' => 'name',
        ])->assertJsonValidationErrors(['name']);

        // Rename a player successfully
        $this->postJson('/admin/players', [
            'pid' => $player->pid,
            'action' => 'name',
            'name' => 'new_name',
        ])->assertJson([
            'code' => 0,
            'message' => trans('admin.players.name.success', ['player' => 'new_name']),
        ]);

        // Single player
        option(['single_player' => true]);
        $this->postJson('/admin/players', [
            'pid' => $player->pid,
            'action' => 'name',
            'name' => 'abc',
        ])->assertJson(['code' => 0]);
        $player->refresh();
        $this->assertEquals('abc', $player->user->nickname);

        // Delete a player
        $this->postJson('/admin/players', [
            'pid' => $player->pid,
            'action' => 'delete',
        ])->assertJson([
            'code' => 0,
            'message' => trans('admin.players.delete.success'),
        ]);
        $this->assertNull(Player::find($player->pid));
    }
}
