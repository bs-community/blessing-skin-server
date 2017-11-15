<?php

use App\Events;
use App\Models\User;
use App\Models\Player;
use App\Models\Texture;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PlayerControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp()
    {
        parent::setUp();
        return $this->actAs('normal');
    }

    public function testIndex()
    {
        $this->visit('/user/player?pid=5')
            ->assertViewHas('players')
            ->assertViewHas('user');
    }

    public function testAdd()
    {
        // Without player name
        $this->post('/user/player/add', [], ['X-Requested-With' => 'XMLHttpRequest'])
            ->seeJson([
                'errno' => 1,
                'msg' => trans('validation.required', ['attribute' => 'Player Name'])
            ]);

        // Not allowed to use Chinese characters
        option(['allow_chinese_playername' => false]);
        $this->post('/user/player/add', [
            'player_name' => '角色名'
        ], ['X-Requested-With' => 'XMLHttpRequest'])
            ->seeJson([
                'errno' => 1,
                'msg' => trans('validation.playername', ['attribute' => 'Player Name'])
            ]);

        // Lack of score
        $user = factory(User::class)->create(['score' => 0]);
        $this->actAs($user)
            ->post('/user/player/add', ['player_name' => 'no_score'])
            ->seeJson([
                'errno' => 7,
                'msg' => trans('user.player.add.lack-score')
            ]);
        $this->expectsEvents(Events\CheckPlayerExists::class);

        // Allowed to use Chinese characters
        option(['allow_chinese_playername' => true]);
        $user = factory(User::class)->create();
        $score = $user->score;
        $this->actAs($user)
            ->post('/user/player/add', [
            'player_name' => '角色名'
        ])->seeJson([
            'errno' => 0,
            'msg' => trans('user.player.add.success', ['name' => '角色名'])
        ]);
        $this->expectsEvents(Events\PlayerWillBeAdded::class);
        $this->expectsEvents(Events\PlayerWasAdded::class);
        $player = Player::where('player_name', '角色名')->first();
        $this->assertNotNull($player);
        $this->assertEquals($user->uid, $player->uid);
        $this->assertEquals('角色名', $player->player_name);
        $this->assertEquals('default', $player->preference);
        $this->assertEquals(
            $score - option('score_per_player'),
            User::find($user->uid)->score
        );

        // Add a existed player
        $this->post('/user/player/add', ['player_name' => '角色名'])
            ->seeJson([
                'errno' => 6,
                'msg' => trans('user.player.add.repeated')
            ]);
    }

    public function testDelete()
    {
        $player = factory(Player::class)->create();
        $user = User::find($player->uid);
        $this->expectsEvents(Events\PlayerWillBeDeleted::class);
        $this->actAs($user)
            ->post('/user/player/delete', ['pid' => $player->pid])
            ->seeJson([
                'errno' => 0,
                'msg' => trans('user.player.delete.success', ['name' => $player->player_name])
            ]);
        $this->assertNull(Player::find($player->pid));
        $this->expectsEvents(Events\PlayerWasDeleted::class);
        $this->assertEquals(
            $user->score + option('score_per_player'),
            User::find($user->uid)->score
        );

        // No returning score
        option(['return_score' => false]);
        $player = factory(Player::class)->create();
        $user = User::find($player->uid);
        $this->actAs($user)
            ->post('/user/player/delete', ['pid' => $player->pid])
            ->seeJson([
                'errno' => 0,
                'msg' => trans('user.player.delete.success', ['name' => $player->player_name])
            ]);
        $this->assertEquals(
            $user->score,
            User::find($user->uid)->score
        );
    }

    public function testShow()
    {
        $player = factory(Player::class)->create(['last_modified' => '2017-11-11 22:51:00']);
        $this->get('/user/player/show?pid='.$player->pid)
            ->seeJson($player->toArray());
    }

    public function testRename()
    {
        $player = factory(Player::class)->create();
        $user = User::find($player->uid);

        // Without new player name
        $this->actAs($user)
            ->post('/user/player/rename', [
                'pid' => $player->pid,
            ], [
                'X-Requested-With' => 'XMLHttpRequest'
            ])->seeJson([
                'errno' => 1,
                'msg' => trans('validation.required', ['attribute' => 'Player Name'])
            ]);

        // Chinese characters are not allowed
        option(['allow_chinese_playername' => false]);
        $this->post('/user/player/rename', [
            'pid' => $player->pid,
            'new_player_name' => '角色名'
        ], [
            'X-Requested-With' => 'XMLHttpRequest'
        ])->seeJson([
            'errno' => 1,
            'msg' => trans('validation.playername', ['attribute' => 'Player Name'])
        ]);

        // Other invalid characters
        option(['allow_chinese_playername' => true]);
        $this->post('/user/player/rename', [
            'pid' => $player->pid,
            'new_player_name' => '\\'
        ], [
            'X-Requested-With' => 'XMLHttpRequest'
        ])->seeJson([
            'errno' => 1,
            'msg' => trans('validation.pname_chinese', ['attribute' => 'Player Name'])
        ]);

        // Use a duplicated player name
        $name = factory(Player::class)->create()->player_name;
        $this->post('/user/player/rename', [
            'pid' => $player->pid,
            'new_player_name' => $name
        ])->seeJson([
            'errno' => 6,
            'msg' => trans('user.player.rename.repeated')
        ]);

        // Success
        $this->expectsEvents(Events\PlayerProfileUpdated::class);
        $this->post('/user/player/rename', [
            'pid' => $player->pid,
            'new_player_name' => 'new_name'
        ])->seeJson([
            'errno' => 0,
            'msg' => trans(
                'user.player.rename.success',
                ['old' => $player->player_name, 'new' => 'new_name']
            )
        ]);
    }

    public function testSetTexture()
    {
        $player = factory(Player::class)->create();
        $user = User::find($player->uid);
        $steve = factory(Texture::class)->create();
        $alex = factory(Texture::class, 'alex')->create();
        $cape = factory(Texture::class, 'cape')->create();

        // Set a not-existed texture
        $this->actAs($user)
            ->post('/user/player/set', [
                'pid' => $player->pid,
                'tid' => ['steve' => -1]
            ])->seeJson([
                'errno' => 6,
                'msg' => trans('skinlib.un-existent')
            ]);

        $this->post('/user/player/set', [
            'pid' => $player->pid,
            'tid' => ['steve' => $steve->tid]
        ])->seeJson([
            'errno' => 0,
            'msg' => trans('user.player.set.success', ['name' => $player->player_name])
        ]);
        $this->expectsEvents(Events\PlayerProfileUpdated::class);
        $this->assertEquals($steve->tid, Player::find($player->pid)->tid_steve);

        $this->post('/user/player/set', [
            'pid' => $player->pid,
            'tid' => ['alex' => $alex->tid]
        ])->seeJson([
            'errno' => 0,
            'msg' => trans('user.player.set.success', ['name' => $player->player_name])
        ]);
        $this->assertEquals($alex->tid, Player::find($player->pid)->tid_alex);

        $this->post('/user/player/set', [
            'pid' => $player->pid,
            'tid' => ['cape' => $cape->tid]
        ])->seeJson([
            'errno' => 0,
            'msg' => trans('user.player.set.success', ['name' => $player->player_name])
        ]);
        $this->assertEquals($cape->tid, Player::find($player->pid)->tid_cape);

        // Invalid texture type is acceptable
        $this->post('/user/player/set', [
            'pid' => $player->pid,
            'tid' => ['nope' => $steve->tid]     // TID must be valid
        ])->seeJson([
            'errno' => 0,
            'msg' => trans('user.player.set.success', ['name' => $player->player_name])
        ]);
    }

    public function testClearTexture()
    {
        $player = factory(Player::class)->create();
        $user = User::find($player->uid);

        $player->setTexture([
            'tid_steve' => 1,
            'tid_alex' => 2,
            'tid_cape' => 3
        ]);
        $player = Player::find($player->pid);

        $this->expectsEvents(Events\PlayerProfileUpdated::class);
        $this->actAs($user)
            ->post('/user/player/texture/clear', [
                'pid' => $player->pid,
                'steve' => 1,    // "1" stands for "true"
                'alex' => 1,
                'cape' => 1,
                'nope' => 1,     // Invalid texture type is acceptable
            ])->seeJson([
                'errno' => 0,
                'msg' => trans('user.player.clear.success', ['name' => $player->player_name])
            ]);
        $this->assertEquals(0, Player::find($player->pid)->tid_steve);
        $this->assertEquals(0, Player::find($player->pid)->tid_alex);
        $this->assertEquals(0, Player::find($player->pid)->tid_cape);
    }

    public function testSetPreference()
    {
        // Without `preference` field
        $player = factory(Player::class)->create();
        $this->actAs(User::find($player->uid))
            ->post('/user/player/preference', [
                'pid' => $player->pid
            ], [
                'X-Requested-With' => 'XMLHttpRequest'
            ])->seeJson([
                'errno' => 1,
                'msg' => trans('validation.required', ['attribute' => 'preference'])
            ]);

        // value of `preference` is invalid
        $this->post('/user/player/preference', [
            'pid' => $player->pid,
            'preference' => 'steve'
        ], [
            'X-Requested-With' => 'XMLHttpRequest'
        ])->seeJson([
            'errno' => 1,
            'msg' => trans('validation.preference', ['attribute' => 'preference'])
        ]);

        // Success
        $this->expectsEvents(Events\PlayerProfileUpdated::class);
        $this->post('/user/player/preference', [
            'pid' => $player->pid,
            'preference' => 'slim'
        ], [
            'X-Requested-With' => 'XMLHttpRequest'
        ])->seeJson([
            'errno' => 0,
            'msg' => trans(
                'user.player.preference.success',
                ['name' => $player->player_name, 'preference' => 'slim']
            )
        ]);
        $this->assertEquals('slim', Player::find($player->pid)->preference);
    }
}
