<?php

namespace Tests;

use App\Events;
use App\Models\User;
use App\Models\Player;
use App\Models\Texture;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PlayerControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actAs('normal');
    }

    public function testIndex()
    {
        $this->get('/user/player?pid=5')
            ->assertViewHas('players')
            ->assertViewHas('user');
    }

    public function testListAll()
    {
        $user = factory(User::class)->create();
        $player = factory(Player::class)->create(['uid' => $user->uid]);
        $this->actingAs($user)
            ->get('/user/player/list')
            ->assertJson([
                [
                    'pid' => $player->pid,
                    'name' => $player->name,
                ],
            ]);
    }

    public function testAdd()
    {
        // Without player name
        $this->postJson('/user/player/add')
            ->assertJson([
                'errno' => 1,
                'msg' => trans('validation.required', ['attribute' => trans('validation.attributes.player_name')]),
            ]);

        // Only A-Za-z0-9_ are allowed
        option(['player_name_rule' => 'official']);
        $this->postJson(
            '/user/player/add',
            ['player_name' => '角色名']
        )->assertJson([
            'errno' => 1,
            'msg' => trans('validation.player_name', ['attribute' => trans('validation.attributes.player_name')]),
        ]);

        // Custom player name rule (regexp)
        option(['player_name_rule' => 'custom']);
        option(['custom_player_name_regexp' => '/^([0-9]+)$/']);
        $this->postJson(
            '/user/player/add',
            ['player_name' => 'yjsnpi']
        )->assertJson([
            'errno' => 1,
            'msg' => trans('validation.player_name', ['attribute' => trans('validation.attributes.player_name')]),
        ]);

        // Lack of score
        option(['player_name_rule' => 'official']);
        $user = factory(User::class)->create(['score' => 0]);
        $this->actAs($user)->postJson(
            '/user/player/add',
            ['player_name' => 'no_score']
        )->assertJson([
            'errno' => 7,
            'msg' => trans('user.player.add.lack-score'),
        ]);
        $this->expectsEvents(Events\CheckPlayerExists::class);

        // Allowed to use CJK characters
        option(['player_name_rule' => 'cjk']);
        $user = factory(User::class)->create();
        $score = $user->score;
        $this->actAs($user)->postJson('/user/player/add', [
            'player_name' => '角色名',
        ])->assertJson([
            'errno' => 0,
            'msg' => trans('user.player.add.success', ['name' => '角色名']),
        ]);
        $this->expectsEvents(Events\PlayerWillBeAdded::class);
        $this->expectsEvents(Events\PlayerWasAdded::class);
        $player = Player::where('name', '角色名')->first();
        $this->assertNotNull($player);
        $this->assertEquals($user->uid, $player->uid);
        $this->assertEquals('角色名', $player->name);
        $this->assertEquals(
            $score - option('score_per_player'),
            User::find($user->uid)->score
        );

        // Add a existed player
        $this->postJson('/user/player/add', ['player_name' => '角色名'])
            ->assertJson([
                'errno' => 6,
                'msg' => trans('user.player.add.repeated'),
            ]);
    }

    public function testDelete()
    {
        $user = factory(User::class)->create();
        $player = factory(Player::class)->create(['uid' => $user->uid]);
        $score = $user->score;
        $this->expectsEvents(Events\PlayerWillBeDeleted::class);
        $this->actingAs($user)
            ->postJson('/user/player/delete', ['pid' => $player->pid])
            ->assertJson([
                'errno' => 0,
                'msg' => trans('user.player.delete.success', ['name' => $player->name]),
            ]);
        $this->assertNull(Player::find($player->pid));
        $this->expectsEvents(Events\PlayerWasDeleted::class);
        $this->assertEquals(
            $score + option('score_per_player'),
            User::find($user->uid)->score
        );

        // No returning score
        option(['return_score' => false]);
        $player = factory(Player::class)->create();
        $user = $player->user;
        $this->actingAs($user)
            ->postJson('/user/player/delete', ['pid' => $player->pid])
            ->assertJson([
                'errno' => 0,
                'msg' => trans('user.player.delete.success', ['name' => $player->name]),
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
            ->assertJson($player->toArray());
    }

    public function testRename()
    {
        $player = factory(Player::class)->create();
        $user = $player->user;

        // Without new player name
        $this->actAs($user)
            ->postJson('/user/player/rename', [
                'pid' => $player->pid,
            ])
            ->assertJson([
                'errno' => 1,
                'msg' => trans('validation.required', ['attribute' => trans('validation.attributes.player_name')]),
            ]);

        // Only A-Za-z0-9_ are allowed
        option(['player_name_rule' => 'official']);
        $this->postJson('/user/player/rename', [
            'pid' => $player->pid,
            'new_player_name' => '角色名',
        ])->assertJson([
            'errno' => 1,
            'msg' => trans('validation.player_name', ['attribute' => trans('validation.attributes.player_name')]),
        ]);

        // Other invalid characters
        option(['player_name_rule' => 'cjk']);
        $this->postJson('/user/player/rename', [
            'pid' => $player->pid,
            'new_player_name' => '\\',
        ])
        ->assertJson([
            'errno' => 1,
            'msg' => trans('validation.player_name', ['attribute' => trans('validation.attributes.player_name')]),
        ]);

        // Use a duplicated player name
        $name = factory(Player::class)->create()->name;
        $this->postJson('/user/player/rename', [
            'pid' => $player->pid,
            'new_player_name' => $name,
        ])->assertJson([
            'errno' => 6,
            'msg' => trans('user.player.rename.repeated'),
        ]);

        // Success
        $this->expectsEvents(Events\PlayerProfileUpdated::class);
        $this->postJson('/user/player/rename', [
            'pid' => $player->pid,
            'new_player_name' => 'new_name',
        ])->assertJson([
            'errno' => 0,
            'msg' => trans(
                'user.player.rename.success',
                ['old' => $player->name, 'new' => 'new_name']
            ),
        ]);
    }

    public function testSetTexture()
    {
        $player = factory(Player::class)->create();
        $user = $player->user;
        $skin = factory(Texture::class)->create();
        $cape = factory(Texture::class, 'cape')->create();

        // Set a not-existed texture
        $this->actAs($user)
            ->postJson('/user/player/set', [
                'pid' => $player->pid,
                'tid' => ['skin' => -1],
            ])->assertJson([
                'errno' => 6,
                'msg' => trans('skinlib.un-existent'),
            ]);

        // Set for "skin" type
        $this->postJson('/user/player/set', [
            'pid' => $player->pid,
            'tid' => ['skin' => $skin->tid],
        ])->assertJson([
            'errno' => 0,
            'msg' => trans('user.player.set.success', ['name' => $player->name]),
        ]);
        $this->assertEquals($skin->tid, Player::find($player->pid)->tid_skin);

        // Set for "cape" type
        $this->postJson('/user/player/set', [
            'pid' => $player->pid,
            'tid' => ['cape' => $cape->tid],
        ])->assertJson([
            'errno' => 0,
            'msg' => trans('user.player.set.success', ['name' => $player->name]),
        ]);
        $this->assertEquals($cape->tid, Player::find($player->pid)->tid_cape);

        // Invalid texture type is acceptable
        $this->postJson('/user/player/set', [
            'pid' => $player->pid,
            'tid' => ['nope' => $skin->tid],     // TID must be valid
        ])->assertJson([
            'errno' => 0,
            'msg' => trans('user.player.set.success', ['name' => $player->name]),
        ]);
    }

    public function testClearTexture()
    {
        $player = factory(Player::class)->create();
        $user = $player->user;

        $player->setTexture([
            'tid_skin' => 1,
            'tid_cape' => 2,
        ]);
        $player = Player::find($player->pid);

        $this->expectsEvents(Events\PlayerProfileUpdated::class);
        $this->actAs($user)
            ->postJson('/user/player/texture/clear', [
                'pid' => $player->pid,
                'skin' => 1,    // "1" stands for "true"
                'cape' => 1,
                'nope' => 1,     // Invalid texture type is acceptable
            ])->assertJson([
                'errno' => 0,
                'msg' => trans('user.player.clear.success', ['name' => $player->name]),
            ]);
        $this->assertEquals(0, Player::find($player->pid)->tid_skin);
        $this->assertEquals(0, Player::find($player->pid)->tid_cape);
    }
}
