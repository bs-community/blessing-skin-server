<?php

namespace Tests;

use App\Models\User;
use App\Models\Player;
use App\Models\Texture;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AdminControllerTest extends BrowserKitTestCase
{
    use DatabaseTransactions;

    protected function setUp()
    {
        // Do not use `WithoutMiddleware` trait
        parent::setUp();
        return $this->actAs('admin');
    }

    public function testIndex()
    {
        $this->visit('/admin')->seePageIs('/admin');
    }

    public function testCustomize()
    {
        // Check if `color_scheme` is existed or not
        $this->get('/admin/customize?action=color', [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest'
        ])->seeJson([
            'errno' => 1,
            'msg' => trans('validation.required', ['attribute' => 'color scheme'])
        ]);

        // Change color
        $this->get('/admin/customize?action=color&color_scheme=purple')
            ->seeJson([
                'errno' => 0,
                'msg' => trans('admin.customize.change-color.success')
            ]);
        $this->assertEquals('purple', option('color_scheme'));

        $this->visit('/admin/customize')
            ->type('url', 'home_pic_url')
            ->type('url', 'favicon_url')
            ->select('1', 'copyright_prefer')
            ->type('copyright', 'copyright_text')
            ->press('submit_homepage');
        $this->assertEquals('url', option('home_pic_url'));
        $this->assertEquals('url', option('favicon_url'));
        $this->assertEquals('1', option('copyright_prefer'));
        $this->assertEquals('copyright', option('copyright_text'));

        $this->visit('/admin/customize')
            ->type('css', 'custom_css')
            ->type('js', 'custom_js')
            ->press('submit_customJsCss');
        $this->assertEquals('css', option('custom_css'));
        $this->assertEquals('js', option('custom_js'));
    }

    public function testScore()
    {
        $this->visit('/admin/score')
            ->type('4', 'score_per_storage')
            ->type('6', 'private_score_per_storage')
            ->type('8', 'score_per_closet_item')
            ->uncheck('return_score')
            ->type('12', 'score_per_player')
            ->type('500', 'user_initial_score')
            ->press('submit_rate');
        $this->assertEquals('4', option('score_per_storage'));
        $this->assertEquals('6', option('private_score_per_storage'));
        $this->assertEquals('8', option('score_per_closet_item'));
        $this->assertFalse(option('return_score'));
        $this->assertEquals('12', option('score_per_player'));
        $this->assertEquals('500', option('user_initial_score'));

        $this->visit('/admin/score')
            ->type('233', 'sign_score_from')
            ->type('666', 'sign_score_to')
            ->type('7', 'sign_gap_time')
            ->check('sign_after_zero')
            ->press('submit_sign');
        $this->assertEquals('233,666', option('sign_score'));
        $this->assertEquals('7', option('sign_gap_time'));
        $this->assertTrue(option('sign_after_zero'));
    }

    public function testOptions()
    {
        $this->visit('/admin/options')
            ->type('My Site', 'site_name')
            ->type('hi', 'site_description')
            ->type('http://blessing.skin/', 'site_url')
            ->uncheck('user_can_register')
            ->type('8', 'regs_per_ip')
            ->select('1', 'ip_get_method')
            ->type('2048', 'max_upload_file_size')
            ->see(trans(
                'options.general.max_upload_file_size.hint',
                ['size' => ini_get('upload_max_filesize')]
            ))
            ->select('cjk', 'player_name_rule')
            ->type('/^([0-9]+)$/', 'custom_player_name_regexp')
            ->select('1', 'api_type')
            ->check('auto_del_invalid_texture')
            ->type('code', 'comment_script')
            ->uncheck('allow_sending_statistics')
            ->press('submit_general');
        $this->assertEquals('My Site', option_localized('site_name'));
        $this->assertEquals('hi', option_localized('site_description'));
        $this->assertEquals('http://blessing.skin', option('site_url'));
        $this->assertFalse(option('user_can_register'));
        $this->assertEquals('8', option('regs_per_ip'));
        $this->assertEquals('1', option('ip_get_method'));
        $this->assertEquals('2048', option('max_upload_file_size'));
        $this->assertEquals('cjk', option('player_name_rule'));
        $this->assertEquals('/^([0-9]+)$/', option('custom_player_name_regexp'));
        $this->assertEquals('1', option('api_type'));
        $this->assertTrue(option('auto_del_invalid_texture'));
        $this->assertEquals('code', option('comment_script'));
        $this->assertFalse(option('allow_sending_statistics'));

        $this->visit('/admin/options')
            ->type('http://blessing.skin/index.php', 'site_url')
            ->press('submit_general');
        $this->assertEquals('http://blessing.skin', option('site_url'));

        $this->visit('/admin/options')
            ->type('announcement', 'announcement')
            ->press('submit_announ');
        $this->assertEquals('announcement', option('announcement'));

        $this->visit('/admin/options')
            ->check('force_ssl')
            ->uncheck('auto_detect_asset_url')
            ->check('return_204_when_notfound')
            ->type('0', 'cache_expire_time')
            ->press('submit_resources');
        $this->assertTrue(option('force_ssl'));
        $this->assertFalse(option('auto_detect_asset_url'));
        $this->assertTrue(option('return_204_when_notfound'));
        $this->assertEquals('0', option('cache_expire_time'));
    }

    public function testUsers()
    {
        $this->visit('/admin/users')->see(trans('general.user-manage'));
    }

    public function testGetUserData()
    {
        $this->getJson('/admin/user-data')
            ->seeJsonStructure([
                'data' => [[
                    'uid',
                    'email',
                    'nickname',
                    'score',
                    'permission',
                    'register_at',
                    'operations',
                    'players_count'
                ]]
            ]);

        $user = factory(User::class)->create();
        $this->getJson('/admin/user-data?uid='.$user->uid)
            ->seeJsonSubset([
                'data' => [[
                    'uid' => $user->uid,
                    'email' => $user->email,
                    'nickname' => $user->nickname,
                    'score' => $user->score,
                    'permission' => $user->permission,
                    'players_count' => 0
                ]]
            ]);
    }

    public function testPlayers()
    {
        $this->visit('/admin/players')->see(trans('general.player-manage'));
    }

    public function testGetPlayerData()
    {
        $player = factory(Player::class)->create();
        $user = $player->user;

        $this->getJson('/admin/player-data')
            ->seeJsonStructure([
                'data' => [[
                    'pid',
                    'uid',
                    'player_name',
                    'preference',
                    'tid_steve',
                    'tid_alex',
                    'tid_cape',
                    'last_modified'
                ]]
            ]);

        $this->getJson('/admin/player-data?uid='.$user->uid)
            ->seeJsonSubset([
                'data' => [[
                    'pid' => $player->pid,
                    'uid' => $user->uid,
                    'player_name' => $player->player_name,
                    'preference' => $player->preference,
                    'tid_steve' => $player->tid_steve,
                    'tid_alex' => $player->tid_alex,
                    'tid_cape' => $player->tid_cape
                ]]
            ]);
    }

    public function testUserAjaxHandler()
    {
        // Operate on an not-existed user
        $this->postJson('/admin/users')
            ->seeJson([
                'errno' => 1,
                'msg' => trans('admin.users.operations.non-existent')
            ]);

        $user = factory(User::class)->create();

        // Operate without `action` field
        $this->postJson('/admin/users', ['uid' => $user->uid])
            ->seeJson([
                'errno' => 1,
                'msg' => trans('admin.users.operations.invalid')
            ]);

        // An admin operating on a super admin should be forbidden
        $superAdmin = factory(User::class, 'superAdmin')->create();
        $this->postJson('/admin/users', ['uid' => $superAdmin->uid])
            ->seeJson([
                'errno' => 1,
                'msg' => trans('admin.users.operations.no-permission')
            ]);

        // Action is `email` but without `email` field
        $this->postJson(
            '/admin/users',
            ['uid' => $user->uid, 'action' => 'email'],
            ['Accept' => 'application/json']
        )->seeJson([
            'errno' => 1,
            'msg' => trans('validation.required', ['attribute' => 'email'])
        ]);

        // Action is `email` but with an invalid email address
        $this->postJson(
            '/admin/users',
            ['uid' => $user->uid, 'action' => 'email', 'email' => 'invalid'],
            ['Accept' => 'application/json']
        )->seeJson([
            'errno' => 1,
            'msg' => trans('validation.email', ['attribute' => 'email'])
        ]);

        // Using an existed email address
        $this->postJson(
            '/admin/users',
            ['uid' => $user->uid, 'action' => 'email', 'email' => $superAdmin->email]
        )->seeJson([
            'errno' => 1,
            'msg' => trans('admin.users.operations.email.existed', ['email' => $superAdmin->email])
        ]);

        // Set email successfully
        $this->postJson(
            '/admin/users',
            ['uid' => $user->uid, 'action' => 'email', 'email' => 'a@b.c']
        )->seeJson([
            'errno' => 0,
            'msg' => trans('admin.users.operations.email.success')
        ]);
        $this->seeInDatabase('users', [
            'uid' => $user->uid,
            'email' => 'a@b.c'
        ]);

        // Toggle verification
        $this->postJson(
            '/admin/users',
            ['uid' => $user->uid, 'action' => 'verification']
        )->seeJson([
            'errno' => 0,
            'msg' => trans('admin.users.operations.verification.success')
        ]);
        $this->seeInDatabase('users', [
            'uid' => $user->uid,
            'verified' => 0
        ]);

        // Action is `nickname` but without `nickname` field
        $this->postJson(
            '/admin/users',
            ['uid' => $user->uid, 'action' => 'nickname'],
            ['Accept' => 'application/json']
        )->seeJson([
            'errno' => 1,
            'msg' => trans('validation.required', ['attribute' => 'nickname'])
        ]);

        // Action is `nickname` but with an invalid nickname
        $this->postJson(
            '/admin/users',
            ['uid' => $user->uid, 'action' => 'nickname', 'nickname' => '\\'],
            ['Accept' => 'application/json']
        )->seeJson([
            'errno' => 1,
            'msg' => trans('validation.no_special_chars', ['attribute' => 'nickname'])
        ]);

        // Set nickname successfully
        $this->postJson(
            '/admin/users',
            ['uid' => $user->uid, 'action' => 'nickname', 'nickname' => 'nickname']
        )->seeJson([
            'errno' => 0,
            'msg' => trans('admin.users.operations.nickname.success', ['new' => 'nickname'])
        ]);
        $this->seeInDatabase('users', [
            'uid' => $user->uid,
            'nickname' => 'nickname'
        ]);

        // Action is `password` but without `password` field
        $this->postJson(
            '/admin/users',
            ['uid' => $user->uid, 'action' => 'password'],
            ['Accept' => 'application/json']
        )->seeJson([
            'errno' => 1,
            'msg' => trans('validation.required', ['attribute' => 'password'])
        ]);

        // Set a too short password
        $this->postJson(
            '/admin/users',
            ['uid' => $user->uid, 'action' => 'password', 'password' => '1'],
            ['Accept' => 'application/json']
        )->seeJson([
            'errno' => 1,
            'msg' => trans('validation.min.string', ['attribute' => 'password', 'min' => 8])
        ]);

        // Set a too long password
        $this->postJson(
            '/admin/users',
            ['uid' => $user->uid, 'action' => 'password', 'password' => str_random(17)],
            ['Accept' => 'application/json']
        )->seeJson([
            'errno' => 1,
            'msg' => trans('validation.max.string', ['attribute' => 'password', 'max' => 16])
        ]);

        // Set password successfully
        $this->postJson(
            '/admin/users',
            ['uid' => $user->uid, 'action' => 'password', 'password' => '12345678']
        )->seeJson([
            'errno' => 0,
            'msg' => trans('admin.users.operations.password.success')
        ]);
        $user = User::find($user->uid);
        $this->assertTrue($user->verifyPassword('12345678'));

        // Action is `score` but without `score` field
        $this->postJson(
            '/admin/users',
            ['uid' => $user->uid, 'action' => 'score'],
            ['Accept' => 'application/json']
        )->seeJson([
            'errno' => 1,
            'msg' => trans('validation.required', ['attribute' => 'score'])
        ]);

        // Action is `score` but with an not-an-integer value
        $this->postJson(
            '/admin/users',
            ['uid' => $user->uid, 'action' => 'score', 'score' => 'string'],
            ['Accept' => 'application/json']
        )->seeJson([
            'errno' => 1,
            'msg' => trans('validation.integer', ['attribute' => 'score'])
        ]);

        // Set score successfully
        $this->postJson(
            '/admin/users',
            ['uid' => $user->uid, 'action' => 'score', 'score' => 123]
        )->seeJson([
            'errno' => 0,
            'msg' => trans('admin.users.operations.score.success')
        ]);
        $this->seeInDatabase('users', [
            'uid' => $user->uid,
            'score' => 123
        ]);

        // Ban a user
        $this->postJson('/admin/users', ['uid' => $user->uid, 'action' => 'ban'])
            ->seeJson([
                'errno' => 0,
                'msg' => trans('admin.users.operations.ban.ban.success'),
                'permission' => User::BANNED
            ]);
        $user = User::find($user->uid);
        $this->assertEquals(User::BANNED, $user->getPermission());

        // Unban a user
        $this->postJson('/admin/users', ['uid' => $user->uid, 'action' => 'ban'])
            ->seeJson([
                'errno' => 0,
                'msg' => trans('admin.users.operations.ban.unban.success'),
                'permission' => User::NORMAL
            ]);
        $user = User::find($user->uid);
        $this->assertEquals(User::NORMAL, $user->getPermission());

        // Set a user to be an admin
        $this->postJson('/admin/users', ['uid' => $user->uid, 'action' => 'admin'])
            ->seeJson([
                'errno' => 0,
                'msg' => trans('admin.users.operations.admin.set.success'),
                'permission' => User::ADMIN
            ]);
        $user = User::find($user->uid);
        $this->assertEquals(User::ADMIN, $user->getPermission());

        // An admin cannot set another admin to be a normal user
        $this->postJson('/admin/users', ['uid' => $user->uid])
            ->seeJson([
                'errno' => 1,
                'msg' => trans('admin.users.operations.no-permission')
            ]);

        // Set an admin to be a normal user
        $this->actAs('superAdmin')
            ->postJson('/admin/users', ['uid' => $user->uid, 'action' => 'admin'])
            ->seeJson([
                'errno' => 0,
                'msg' => trans('admin.users.operations.admin.unset.success'),
                'permission' => User::NORMAL
            ]);
        $user = User::find($user->uid);
        $this->assertEquals(User::NORMAL, $user->getPermission());

        // Delete a user
        $this->postJson('/admin/users', ['uid' => $user->uid, 'action' => 'delete'])
            ->seeJson([
                'errno' => 0,
                'msg' => trans('admin.users.operations.delete.success')
            ]);
        $this->assertNull(User::find($user->uid));
    }

    public function testPlayerAjaxHandler()
    {
        $player = factory(Player::class)->create();

        // Operate on a not-existed player
        $this->postJson('/admin/players', ['pid' => -1])
            ->seeJson([
                'errno' => 1,
                'msg' => trans('general.unexistent-player')
            ]);

        // An admin cannot operate another admin's player
        $admin = factory(User::class, 'admin')->create();
        $this->postJson(
            '/admin/players',
            ['pid' => factory(Player::class)->create(['uid' => $admin->uid])->pid]
        )->seeJson([
            'errno' => 1,
            'msg' => trans('admin.players.no-permission')
        ]);
        $superAdmin = factory(User::class, 'superAdmin')->create();
        $this->postJson(
            '/admin/players',
            ['pid' => factory(Player::class)->create(['uid' => $superAdmin->uid])->pid]
        )->seeJson([
            'errno' => 1,
            'msg' => trans('admin.players.no-permission')
        ]);
        // For self is OK
        $this->actAs($admin)->postJson(
            '/admin/players',
            ['pid' => factory(Player::class)->create(['uid' => $admin->uid])->pid]
        )->seeJson([
            'errno' => 1,
            'msg' => trans('admin.users.operations.invalid')
        ]);

        // Change preference without `preference` field
        $this->postJson('/admin/players', [
            'pid' => $player->pid,
            'action' => 'preference'
        ], [
            'Accept' => 'application/json',
        ])->seeJson([
            'errno' => 1,
            'msg' => trans('validation.required', ['attribute' => 'preference'])
        ]);

        // Change preference but neither `default` nor `slim`
        $this->postJson('/admin/players', [
            'pid' => $player->pid,
            'action' => 'preference',
            'preference' => 'steve'
        ], [
            'Accept' => 'application/json',
        ])->seeJson([
            'errno' => 1,
            'msg' => trans('validation.preference', ['attribute' => 'preference'])
        ]);

        // Set preference successfully
        $this->postJson('/admin/players', [
            'pid' => $player->pid,
            'action' => 'preference',
            'preference' => 'slim'
        ])->seeJson([
            'errno' => 0,
            'msg' => trans(
                'admin.players.preference.success',
                ['player' => $player->player_name, 'preference' => 'slim']
            )
        ]);
        $player = Player::find($player->pid);
        $this->assertEquals('slim', $player->preference);

        // Change texture without `model` field
        $this->postJson('/admin/players', [
            'pid' => $player->pid,
            'action' => 'texture'
        ], [
            'Accept' => 'application/json',
        ])->seeJson([
            'errno' => 1,
            'msg' => trans('validation.required', ['attribute' => 'model'])
        ]);

        // Change texture with invalid model name
        $this->postJson('/admin/players', [
            'pid' => $player->pid,
            'action' => 'texture',
            'model' => 'slim'
        ], [
            'Accept' => 'application/json',
        ])->seeJson([
            'errno' => 1,
            'msg' => trans('validation.model', ['attribute' => 'model'])
        ]);

        // Change texture without `tid` field
        $this->postJson('/admin/players', [
            'pid' => $player->pid,
            'action' => 'texture',
            'model' => 'steve'
        ], [
            'Accept' => 'application/json',
        ])->seeJson([
            'errno' => 1,
            'msg' => trans('validation.required', ['attribute' => 'tid'])
        ]);

        // Change texture with a not-integer value
        $this->postJson('/admin/players', [
            'pid' => $player->pid,
            'action' => 'texture',
            'model' => 'steve',
            'tid' => 'string'
        ], [
            'Accept' => 'application/json',
        ])->seeJson([
            'errno' => 1,
            'msg' => trans('validation.integer', ['attribute' => 'tid'])
        ]);

        // Invalid texture
        $this->postJson('/admin/players', [
            'pid' => $player->pid,
            'action' => 'texture',
            'model' => 'steve',
            'tid' => -1
        ])->seeJson([
            'errno' => 1,
            'msg' => trans('admin.players.textures.non-existent', ['tid' => -1])
        ]);

        $steve = factory(Texture::class)->create();
        $alex = factory(Texture::class, 'alex')->create();
        $cape = factory(Texture::class, 'cape')->create();

        // Steve
        $this->postJson('/admin/players', [
            'pid' => $player->pid,
            'action' => 'texture',
            'model' => 'steve',
            'tid' => $steve->tid
        ])->seeJson([
            'errno' => 0,
            'msg' => trans('admin.players.textures.success', ['player' => $player->player_name])
        ]);
        $player = Player::find($player->pid);
        $this->assertEquals($steve->tid, $player->tid_steve);

        // Alex
        $this->postJson('/admin/players', [
            'pid' => $player->pid,
            'action' => 'texture',
            'model' => 'alex',
            'tid' => $alex->tid
        ])->seeJson([
            'errno' => 0,
            'msg' => trans('admin.players.textures.success', ['player' => $player->player_name])
        ]);
        $player = Player::find($player->pid);
        $this->assertEquals($alex->tid, $player->tid_alex);

        // Cape
        $this->postJson('/admin/players', [
            'pid' => $player->pid,
            'action' => 'texture',
            'model' => 'cape',
            'tid' => $cape->tid
        ])->seeJson([
            'errno' => 0,
            'msg' => trans('admin.players.textures.success', ['player' => $player->player_name])
        ]);
        $player = Player::find($player->pid);
        $this->assertEquals($cape->tid, $player->tid_cape);

        // Reset texture
        $this->postJson('/admin/players', [
            'pid' => $player->pid,
            'action' => 'texture',
            'model' => 'steve',
            'tid' => 0
        ])->seeJson([
            'errno' => 0,
            'msg' => trans('admin.players.textures.success', ['player' => $player->player_name])
        ]);
        $player = Player::find($player->pid);
        $this->assertEquals(0, $player->tid_steve);
        $this->assertNotEquals(0, $player->tid_alex);
        $this->assertNotEquals(0, $player->tid_cape);
        $this->postJson('/admin/players', [
            'pid' => $player->pid,
            'action' => 'texture',
            'model' => 'alex',
            'tid' => 0
        ])->seeJson([
            'errno' => 0,
            'msg' => trans('admin.players.textures.success', ['player' => $player->player_name])
        ]);
        $player = Player::find($player->pid);
        $this->assertEquals(0, $player->tid_alex);
        $this->assertNotEquals(0, $player->tid_cape);
        $this->postJson('/admin/players', [
            'pid' => $player->pid,
            'action' => 'texture',
            'model' => 'cape',
            'tid' => 0
        ])->seeJson([
            'errno' => 0,
            'msg' => trans('admin.players.textures.success', ['player' => $player->player_name])
        ]);
        $player = Player::find($player->pid);
        $this->assertEquals(0, $player->tid_cape);

        // Change owner without `uid` field
        $this->postJson('/admin/players', [
            'pid' => $player->pid,
            'action' => 'owner'
        ], [
            'Accept' => 'application/json',
        ])->seeJson([
            'errno' => 1,
            'msg' => trans('validation.required', ['attribute' => 'uid'])
        ]);

        // Change owner with a not-integer `uid` value
        $this->postJson('/admin/players', [
            'pid' => $player->pid,
            'action' => 'owner',
            'uid' => 'string'
        ], [
            'Accept' => 'application/json',
        ])->seeJson([
            'errno' => 1,
            'msg' => trans('validation.integer', ['attribute' => 'uid'])
        ]);

        // Change owner to a not-existed user
        $this->postJson('/admin/players', [
            'pid' => $player->pid,
            'action' => 'owner',
            'uid' => -1
        ])->seeJson([
            'errno' => 1,
            'msg' => trans('admin.users.operations.non-existent')
        ]);

        // Change owner successfully
        $user = factory(User::class)->create();
        $this->postJson('/admin/players', [
            'pid' => $player->pid,
            'action' => 'owner',
            'uid' => $user->uid
        ])->seeJson([
            'errno' => 0,
            'msg' => trans(
                'admin.players.owner.success',
                ['player' => $player->player_name, 'user' => $user->nickname]
            )
        ]);

        // Rename a player without `name` field
        $this->postJson('/admin/players', [
            'pid' => $player->pid,
            'action' => 'name'
        ], [
            'Accept' => 'application/json',
        ])->seeJson([
            'errno' => 1,
            'msg' => trans('validation.required', ['attribute' => 'name'])
        ]);

        // Rename a player successfully
        $this->postJson('/admin/players', [
            'pid' => $player->pid,
            'action' => 'name',
            'name' => 'new_name'
        ])->seeJson([
            'errno' => 0,
            'msg' => trans('admin.players.name.success', ['player' => 'new_name']),
            'name' => 'new_name'
        ]);

        // Delete a player
        $this->postJson('/admin/players', [
            'pid' => $player->pid,
            'action' => 'delete'
        ])->seeJson([
            'errno' => 0,
            'msg' => trans('admin.players.delete.success')
        ]);
        $this->assertNull(Player::find($player->pid));
    }

    public function testGetOneUser()
    {
        $user = factory(User::class)->create();
        $this->get('/admin/user/'.$user->uid)
            ->seeJson([
                'errno' => 0,
                'msg' => 'success',
                'user' => [
                    'uid' => $user->uid,
                    'email' => $user->email,
                    'nickname' => $user->nickname,
                    'score' => $user->score,
                    'avatar' => $user->avatar,
                    'permission' => $user->permission,
                    'verified' => (bool) $user->verified,
                    'verification_token' => (string) $user->verification_token
                ]
            ]);

        $this->get('/admin/user/-1')
            ->seeJson([
                'errno' => 1,
                'msg' => 'No such user.'
            ]);
    }
}
