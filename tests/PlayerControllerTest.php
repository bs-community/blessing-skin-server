<?php

namespace Tests;

use Event;
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
        $this->get('/user/player?pid=5')->assertViewIs('user.player');
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
        $this->postJson('/user/player/add')->assertJsonValidationErrors('player_name');

        // Only A-Za-z0-9_ are allowed
        option(['player_name_rule' => 'official']);
        $this->postJson(
            '/user/player/add',
            ['player_name' => '角色名']
        )->assertJsonValidationErrors('player_name');

        // Custom player name rule (regexp)
        option(['player_name_rule' => 'custom']);
        option(['custom_player_name_regexp' => '/^([0-9]+)$/']);
        $this->postJson(
            '/user/player/add',
            ['player_name' => 'yjsnpi']
        )->assertJsonValidationErrors('player_name');

        // Lack of score
        option(['player_name_rule' => 'official']);
        $user = factory(User::class)->create(['score' => 0]);
        $this->actingAs($user)->postJson(
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
        $this->actingAs($user)->postJson('/user/player/add', [
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

        // Single player
        option(['single_player' => true]);
        $this->postJson('/user/player/add', ['player_name' => 'abc'])
            ->assertJson([
                'errno' => 1,
                'msg' => trans('user.player.add.single'),
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

        // Single player
        option(['single_player' => true]);
        $player = factory(Player::class)->create(['uid' => $user->uid]);
        $this->actingAs($user)
            ->postJson('/user/player/delete', ['pid' => $player->pid])
            ->assertJson([
                'errno' => 1,
                'msg' => trans('user.player.delete.single'),
            ]);
        $this->assertNotNull(Player::find($player->pid));
    }

    public function testShow()
    {
        $player = factory(Player::class)->create(['last_modified' => '2017-11-11 22:51:00']);
        $this->get('/user/player/show?pid='.$player->pid)
            ->assertJson($player->toArray());
    }

    public function testRename()
    {
        Event::fake();
        $player = factory(Player::class)->create();
        $user = $player->user;

        // Without new player name
        $this->actingAs($user)
            ->postJson('/user/player/rename', [
                'pid' => $player->pid,
            ])
            ->assertJsonValidationErrors('new_player_name');

        // Only A-Za-z0-9_ are allowed
        option(['player_name_rule' => 'official']);
        $this->postJson('/user/player/rename', [
            'pid' => $player->pid,
            'new_player_name' => '角色名',
        ])->assertJsonValidationErrors('new_player_name');

        // Other invalid characters
        option(['player_name_rule' => 'cjk']);
        $this->postJson('/user/player/rename', [
            'pid' => $player->pid,
            'new_player_name' => '\\',
        ])->assertJsonValidationErrors('new_player_name');

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
        Event::assertDispatched(Events\PlayerProfileUpdated::class);

        // Single player
        option(['single_player' => true]);
        $this->postJson('/user/player/rename', [
            'pid' => $player->pid,
            'new_player_name' => 'abc',
        ])->assertJson(['errno' => 0]);
        $this->assertEquals('abc', $player->user->nickname);
    }

    public function testSetTexture()
    {
        $player = factory(Player::class)->create();
        $user = $player->user;
        $skin = factory(Texture::class)->create();
        $cape = factory(Texture::class, 'cape')->create();

        // Set a not-existed texture
        $this->actingAs($user)
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
        Event::fake();
        $player = factory(Player::class)->create();
        $user = $player->user;

        $player->tid_skin = 1;
        $player->tid_cape = 2;
        $player->save();
        $player->refresh();

        $this->actingAs($user)
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
        Event::assertDispatched(Events\PlayerProfileUpdated::class);
    }

    public function testBind()
    {
        Event::fake();
        option(['single_player' => true]);
        $user = factory(User::class)->create();

        $this->actingAs($user)
            ->postJson('/user/player/bind')
            ->assertJsonValidationErrors('player');

        $this->postJson('/user/player/bind', ['player' => 'abc'])
            ->assertJson([
                'errno' => 0,
                'msg' => trans('user.player.bind.success'),
            ]);
        Event::assertDispatched(Events\CheckPlayerExists::class);
        Event::assertDispatched(Events\PlayerWillBeAdded::class);
        Event::assertDispatched(Events\PlayerWasAdded::class);
        $player = Player::where('name', 'abc')->first();
        $this->assertNotNull($player);
        $this->assertEquals($user->uid, $player->uid);
        $this->assertEquals('abc', $player->name);
        $user->refresh();
        $this->assertEquals('abc', $user->nickname);

        $player2 = factory(Player::class)->create();
        $player3 = factory(Player::class)->create(['uid' => $user->uid]);
        $this->postJson('/user/player/bind', ['player' => $player2->name])
            ->assertJson([
                'errno' => 1,
                'msg' => trans('user.player.rename.repeated'),
            ]);

        $this->postJson('/user/player/bind', ['player' => $player->name])
            ->assertJson(['errno' => 0]);
        $this->assertNull(Player::where('name', $player3->name)->first());
    }
}
