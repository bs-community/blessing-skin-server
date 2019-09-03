<?php

namespace Tests;

use Event;
use App\Events;
use App\Models\User;
use App\Models\Player;
use App\Models\Texture;
use App\Services\Filter;
use App\Services\Rejection;
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
            ->assertJson(['data' => [
                [
                    'pid' => $player->pid,
                    'name' => $player->name,
                ],
            ]]);
    }

    public function testAdd()
    {
        Event::fake();

        // Without player name
        $this->postJson('/user/player/add')->assertJsonValidationErrors('name');

        // Only A-Za-z0-9_ are allowed
        option(['player_name_rule' => 'official']);
        $this->postJson(
            '/user/player/add',
            ['name' => '角色名']
        )->assertJsonValidationErrors('name');

        // Custom player name rule (regexp)
        option(['player_name_rule' => 'custom']);
        option(['custom_player_name_regexp' => '/^([0-9]+)$/']);
        $this->postJson(
            '/user/player/add',
            ['name' => 'yjsnpi']
        )->assertJsonValidationErrors('name');

        // Lack of score
        option(['player_name_rule' => 'official']);
        $user = factory(User::class)->create(['score' => 0]);
        $this->actingAs($user)->postJson(
            '/user/player/add',
            ['name' => 'no_score']
        )->assertJson([
            'code' => 7,
            'message' => trans('user.player.add.lack-score'),
        ]);
        Event::assertDispatched(Events\CheckPlayerExists::class);

        // Allowed to use CJK characters
        option(['player_name_rule' => 'cjk']);
        $user = factory(User::class)->create();
        $score = $user->score;
        $this->actingAs($user)->postJson('/user/player/add', [
            'name' => '角色名',
        ])->assertJson([
            'code' => 0,
            'message' => trans('user.player.add.success', ['name' => '角色名']),
        ]);
        Event::assertDispatched(Events\PlayerWillBeAdded::class);
        Event::assertDispatched(Events\PlayerWasAdded::class);
        $player = Player::where('name', '角色名')->first();
        $this->assertNotNull($player);
        $this->assertEquals($user->uid, $player->uid);
        $this->assertEquals('角色名', $player->name);
        $this->assertEquals(
            $score - option('score_per_player'),
            User::find($user->uid)->score
        );

        // Add a existed player
        $this->postJson('/user/player/add', ['name' => '角色名'])
            ->assertJson([
                'code' => 6,
                'message' => trans('user.player.add.repeated'),
            ]);

        // Single player
        option(['single_player' => true]);
        $this->postJson('/user/player/add', ['name' => 'abc'])
            ->assertJson([
                'code' => 1,
                'message' => trans('user.player.add.single'),
            ]);
    }

    public function testDelete()
    {
        Event::fake();

        $user = factory(User::class)->create();
        $player = factory(Player::class)->create(['uid' => $user->uid]);
        $score = $user->score;
        $this->actingAs($user)
            ->postJson('/user/player/delete/'.$player->pid)
            ->assertJson([
                'code' => 0,
                'message' => trans('user.player.delete.success', ['name' => $player->name]),
            ]);
        $this->assertNull(Player::find($player->pid));
        Event::assertDispatched(Events\PlayerWillBeDeleted::class);
        Event::assertDispatched(Events\PlayerWasDeleted::class);
        $this->assertEquals(
            $score + option('score_per_player'),
            User::find($user->uid)->score
        );

        // No returning score
        option(['return_score' => false]);
        $player = factory(Player::class)->create();
        $user = $player->user;
        $this->actingAs($user)
            ->postJson('/user/player/delete/'.$player->pid)
            ->assertJson([
                'code' => 0,
                'message' => trans('user.player.delete.success', ['name' => $player->name]),
            ]);
        $this->assertEquals(
            $user->score,
            User::find($user->uid)->score
        );

        // Single player
        option(['single_player' => true]);
        $player = factory(Player::class)->create(['uid' => $user->uid]);
        $this->actingAs($user)
            ->postJson('/user/player/delete/'.$player->pid)
            ->assertJson([
                'code' => 1,
                'message' => trans('user.player.delete.single'),
            ]);
        $this->assertNotNull(Player::find($player->pid));
    }

    public function testRename()
    {
        Event::fake();
        $player = factory(Player::class)->create();
        $user = $player->user;

        // Without new player name
        $this->actingAs($user)
            ->postJson('/user/player/rename/'.$player->pid)
            ->assertJsonValidationErrors('name');

        // Only A-Za-z0-9_ are allowed
        option(['player_name_rule' => 'official']);
        $this->postJson('/user/player/rename/'.$player->pid, ['name' => '角色名'])
            ->assertJsonValidationErrors('name');

        // Other invalid characters
        option(['player_name_rule' => 'cjk']);
        $this->postJson('/user/player/rename/'.$player->pid, ['name' => '\\'])
            ->assertJsonValidationErrors('name');

        // Use a duplicated player name
        $name = factory(Player::class)->create()->name;
        $this->postJson('/user/player/rename/'.$player->pid, ['name' => $name])
            ->assertJson([
                'code' => 6,
                'message' => trans('user.player.rename.repeated'),
            ]);
        Event::assertDispatched('player.renaming');

        // Rejected by filter
        $filter = resolve(Filter::class);
        $pid = $player->pid;
        $filter->add('user_can_rename_player', function ($can, $player, $newName) {
            $this->assertEquals('new', $newName);
            return new Rejection('rejected');
        });
        $name = factory(Player::class)->create()->name;
        $this->postJson('/user/player/rename/'.$player->pid, ['name' => 'new'])
            ->assertJson([
                'code' => 1,
                'message' => 'rejected',
            ]);
        $filter->remove('user_can_rename_player');

        // Success
        Event::fake();
        $this->postJson('/user/player/rename/'.$pid, ['name' => 'new_name'])
            ->assertJson([
                'code' => 0,
                'message' => trans(
                    'user.player.rename.success',
                    ['old' => $player->name, 'new' => 'new_name']
                ),
            ]);
        Event::assertDispatched(Events\PlayerProfileUpdated::class);
        Event::assertDispatched('player.renaming', function ($event, $payload) use ($pid) {
            [$player, $newName] = $payload;
            $this->assertEquals($pid, $player->pid);
            $this->assertEquals('new_name', $newName);
            return true;
        });
        Event::assertDispatched('player.renamed', function ($event, $payload) use ($pid) {
            [$player, $oldName] = $payload;
            $this->assertEquals($pid, $player->pid);
            $this->assertNotEquals('new_name', $oldName);
            return true;
        });

        // Single player
        option(['single_player' => true]);
        $this->postJson('/user/player/rename/'.$player->pid, ['name' => 'abc'])
            ->assertJson(['code' => 0]);
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
            ->postJson('/user/player/set/'.$player->pid, ['skin' => -1])
            ->assertJson([
                'code' => 1,
                'message' => trans('skinlib.non-existent'),
            ]);

        // Set for "skin" type
        $this->postJson('/user/player/set/'.$player->pid, ['skin' => $skin->tid])
            ->assertJson([
                'code' => 0,
                'message' => trans('user.player.set.success', ['name' => $player->name]),
            ]);
        $this->assertEquals($skin->tid, Player::find($player->pid)->tid_skin);

        // Set for "cape" type
        $this->postJson('/user/player/set/'.$player->pid, ['cape' => $cape->tid])
            ->assertJson([
                'code' => 0,
                'message' => trans('user.player.set.success', ['name' => $player->name]),
            ]);
        $this->assertEquals($cape->tid, Player::find($player->pid)->tid_cape);
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
            ->postJson('/user/player/texture/clear/'.$player->pid, [
                'skin' => 1,    // "1" stands for "true"
                'cape' => 1,
                'nope' => 1,     // Invalid texture type is acceptable
            ])->assertJson([
                'code' => 0,
                'message' => trans('user.player.clear.success', ['name' => $player->name]),
            ]);
        $this->assertEquals(0, Player::find($player->pid)->tid_skin);
        $this->assertEquals(0, Player::find($player->pid)->tid_cape);
        Event::assertDispatched(Events\PlayerProfileUpdated::class);

        $this->postJson('/user/player/texture/clear/'.$player->pid, ['type' => ['skin']])
            ->assertJson(['code' => 0]);
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
                'code' => 0,
                'message' => trans('user.player.bind.success'),
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
                'code' => 1,
                'message' => trans('user.player.rename.repeated'),
            ]);

        $this->postJson('/user/player/bind', ['player' => $player->name])
            ->assertJson(['code' => 0]);
        $this->assertNull(Player::where('name', $player3->name)->first());
    }
}
