<?php

namespace Tests;

use App\Events;
use App\Models\Player;
use App\Models\Texture;
use App\Models\User;
use Blessing\Rejection;
use Event;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PlayerControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    public function testIndex()
    {
        $filter = Fakes\Filter::fake();

        $this->get('/user/player?pid=5')->assertViewIs('user.player');
        $filter->assertApplied('grid:user.player');
    }

    public function testList()
    {
        $user = User::factory()->create();
        $player = Player::factory()->create(['uid' => $user->uid]);
        $this->actingAs($user)
            ->get('/user/player/list')
            ->assertJson([$player->toArray()]);
    }

    public function testAccessControl()
    {
        $user = User::factory()->make();
        $player = Player::factory()->create();

        $this->actingAs($user)
            ->deleteJson(route('user.player.delete', ['player' => $player]))
            ->assertJson([
                'code' => 1,
                'message' => trans('admin.players.no-permission'),
            ])
            ->assertForbidden();
    }

    public function testAdd()
    {
        Event::fake();
        $filter = Fakes\Filter::fake();

        // Without player name
        $this->postJson(route('user.player.add'))->assertJsonValidationErrors('name');

        // Only A-Za-z0-9_ are allowed
        option(['player_name_rule' => 'official']);
        $this->postJson(
            route('user.player.add'),
            ['name' => '角色名']
        )->assertJsonValidationErrors('name');

        // Custom player name rule (regexp)
        option(['player_name_rule' => 'custom']);
        option(['custom_player_name_regexp' => '/^\d+$/']);
        $this->postJson(
            route('user.player.add'),
            ['name' => 'yjsnpi']
        )->assertJsonValidationErrors('name');

        // with an existed player name
        option(['player_name_rule' => 'official']);
        $existed = Player::factory()->create();
        $this->postJson(route('user.player.add'), ['name' => $existed->name])
            ->assertJsonValidationErrors('name');

        // Lack of score
        $user = User::factory()->create(['score' => 0]);
        $this->actingAs($user)->postJson(
            route('user.player.add'),
            ['name' => 'no_score']
        )->assertJson([
            'code' => 7,
            'message' => trans('user.player.add.lack-score'),
        ]);
        $filter->assertApplied('new_player_name', function ($name) {
            $this->assertEquals('no_score', $name);

            return true;
        });
        Event::assertDispatched('player.add.attempt', function ($event, $payload) use ($user) {
            $this->assertEquals('no_score', $payload[0]);
            $this->assertEquals($user->uid, $payload[1]->uid);

            return true;
        });
        Event::assertNotDispatched('player.adding');
        Event::assertNotDispatched('player.added');

        // rejected
        Event::fake();
        $filter->add('can_add_player', function ($can, $name) {
            $this->assertEquals('can', $name);

            return new Rejection('rejected');
        });
        $this->postJson(
            route('user.player.add'),
            ['name' => 'can']
        )->assertJson(['code' => 1, 'message' => 'rejected']);
        Event::assertDispatched('player.add.attempt');
        Event::assertNotDispatched('player.adding');
        Event::assertNotDispatched('player.added');
        $filter->remove('can_add_player');

        // Allowed to use CJK characters
        Event::fake();
        option(['player_name_rule' => 'cjk']);
        $user = User::factory()->create();
        $score = $user->score;
        $this->actingAs($user)->postJson(route('user.player.add'), [
            'name' => '角色名',
        ])->assertJson([
            'code' => 0,
            'message' => trans('user.player.add.success', ['name' => '角色名']),
        ]);
        Event::assertDispatched('player.add.attempt', function ($event, $payload) use ($user) {
            $this->assertEquals('角色名', $payload[0]);
            $this->assertEquals($user->uid, $payload[1]->uid);

            return true;
        });
        Event::assertDispatched('player.adding', function ($event, $payload) use ($user) {
            $this->assertEquals('角色名', $payload[0]);
            $this->assertEquals($user->uid, $payload[1]->uid);

            return true;
        });
        Event::assertDispatched('player.added', function ($event, $payload) use ($user) {
            $this->assertEquals('角色名', $payload[0]->name);
            $this->assertEquals($user->uid, $payload[1]->uid);

            return true;
        });
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
    }

    public function testDelete()
    {
        Event::fake();
        $filter = Fakes\Filter::fake();

        $user = User::factory()->create();
        $player = Player::factory()->create(['uid' => $user->uid]);
        $score = $user->score;

        // rejected
        $filter->add('can_delete_player', function ($can, $p) use ($player) {
            $this->assertTrue($player->is($p));

            return new Rejection('rejected');
        });
        $this->actingAs($user)
            ->deleteJson(route('user.player.delete', ['player' => $player]))
            ->assertJson(['code' => 1, 'message' => 'rejected']);
        $filter->remove('can_delete_player');

        // success
        $this->deleteJson(route('user.player.delete', ['player' => $player]))
            ->assertJson([
                'code' => 0,
                'message' => trans('user.player.delete.success', ['name' => $player->name]),
            ]);
        Event::assertDispatched('player.delete.attempt', function ($event, $payload) use ($player, $user) {
            $this->assertEquals($player->pid, $payload[0]->pid);
            $this->assertEquals($user->uid, $payload[1]->uid);

            return true;
        });
        Event::assertDispatched('player.deleting', function ($event, $payload) use ($player, $user) {
            $this->assertEquals($player->pid, $payload[0]->pid);
            $this->assertEquals($user->uid, $payload[1]->uid);

            return true;
        });
        $this->assertNull(Player::find($player->pid));
        Event::assertDispatched('player.deleted', function ($event, $payload) use ($player, $user) {
            $this->assertEquals($player->pid, $payload[0]->pid);
            $this->assertEquals($user->uid, $payload[1]->uid);

            return true;
        });
        Event::assertDispatched(Events\PlayerWillBeDeleted::class);
        Event::assertDispatched(Events\PlayerWasDeleted::class);
        $this->assertEquals(
            $score + option('score_per_player'),
            User::find($user->uid)->score
        );

        // No returning score
        option(['return_score' => false]);
        $player = Player::factory()->create();
        $user = $player->user;
        $this->actingAs($user)
            ->deleteJson(route('user.player.delete', ['player' => $player]))
            ->assertJson([
                'code' => 0,
                'message' => trans('user.player.delete.success', ['name' => $player->name]),
            ]);
        $this->assertEquals(
            $user->score,
            User::find($user->uid)->score
        );
    }

    public function testRename()
    {
        Event::fake();
        $player = Player::factory()->create();
        $user = $player->user;

        // Without new player name
        $this->actingAs($user)
            ->putJson(route('user.player.rename', ['player' => $player]))
            ->assertJsonValidationErrors('name');

        // Only A-Za-z0-9_ are allowed
        option(['player_name_rule' => 'official']);
        $this->putJson(
                route('user.player.rename', ['player' => $player]),
                ['name' => '角色名']
            )->assertJsonValidationErrors('name');

        // Other invalid characters
        option(['player_name_rule' => 'cjk']);
        $this->putJson(
                route('user.player.rename', ['player' => $player]),
                ['name' => '\\']
            )->assertJsonValidationErrors('name');

        // with an existed player name
        $existed = Player::factory()->create();
        $this->putJson(
                route('user.player.rename', ['player' => $player]),
                ['name' => $existed->name]
            )->assertJsonValidationErrors('name');

        // Rejected by filter
        $filter = Fakes\Filter::fake();
        $filter->add('can_rename_player', function ($can, $p, $name) use ($player) {
            $this->assertTrue($player->is($p));
            $this->assertEquals('new', $name);

            return new Rejection('rejected');
        });
        Player::factory()->create()->name;
        $this->putJson(
            route('user.player.rename', ['player' => $player]),
            ['name' => 'new']
        )->assertJson([
            'code' => 1,
            'message' => 'rejected',
        ]);
        $filter->remove('can_rename_player');

        // Success
        Event::fake();
        $pid = $player->pid;
        $this->putJson(
            route('user.player.rename', ['player' => $player]),
            ['name' => 'new_name']
        )->assertJson([
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
        Event::assertDispatched('player.renamed', function ($event, $payload) use ($player) {
            $this->assertTrue($player->fresh()->is($payload[0]));
            $this->assertNotEquals('new_name', $payload[1]->name);

            return true;
        });
        $filter->assertApplied('new_player_name', function ($name) {
            $this->assertEquals('new_name', $name);

            return true;
        });
    }

    public function testSetTexture()
    {
        $player = Player::factory()->create();
        $user = $player->user;
        $skin = Texture::factory()->create();
        $cape = Texture::factory()->cape()->create();

        // rejected
        $filter = Fakes\Filter::fake();
        $filter->add('can_set_texture', function ($can, $p, $type, $tid) use ($player) {
            $this->assertTrue($player->is($p));
            $this->assertEquals('skin', $type);
            $this->assertEquals(-1, $tid);

            return new Rejection('rejected');
        });
        $this->actingAs($user)
            ->putJson(route('user.player.set', ['player' => $player]), ['skin' => -1])
            ->assertJson(['code' => 1, 'message' => 'rejected']);
        $filter->remove('can_set_texture');

        // set a not-existed texture
        $this->putJson(route('user.player.set', ['player' => $player]), ['skin' => -1])
            ->assertJson([
                'code' => 1,
                'message' => trans('skinlib.non-existent'),
            ]);

        // set a private texture
        $private = Texture::factory()->private()->create();
        $this->putJson(
                route('user.player.set', ['player' => $player]),
                ['skin' => $private->tid]
            )->assertJson([
                'code' => 1,
                'message' => trans('user.closet.remove.non-existent'),
            ]);

        // set for "skin" type
        Event::fake();
        $user->closet()->attach($skin->tid, ['item_name' => $skin->name]);
        $this->putJson(
            route('user.player.set', ['player' => $player]),
            ['skin' => $skin->tid]
        )->assertJson([
            'code' => 0,
            'message' => trans('user.player.set.success', ['name' => $player->name]),
        ]);
        $this->assertEquals($skin->tid, Player::find($player->pid)->tid_skin);
        Event::assertDispatched(
            'player.texture.updating',
            function ($event, $payload) use ($player, $skin) {
                $this->assertEquals($player->pid, $payload[0]->pid);
                $this->assertEquals($skin->tid, $payload[1]->tid);

                return true;
            }
        );
        Event::assertDispatched(
            'player.texture.updated',
            function ($event, $payload) use ($player, $skin) {
                $this->assertEquals($player->pid, $payload[0]->pid);
                $this->assertEquals($skin->tid, $payload[0]->tid_skin);
                $this->assertEquals($skin->tid, $payload[1]->tid);

                return true;
            }
        );

        // set for "cape" type
        Event::fake();
        $user->closet()->attach($cape->tid, ['item_name' => $cape->name]);
        $this->putJson(
            route('user.player.set', ['player' => $player]),
            ['cape' => $cape->tid]
        )->assertJson([
            'code' => 0,
            'message' => trans('user.player.set.success', ['name' => $player->name]),
        ]);
        $this->assertEquals($cape->tid, Player::find($player->pid)->tid_cape);
    }

    public function testClearTexture()
    {
        Event::fake();
        $player = Player::factory()->create();
        $user = $player->user;

        $player->tid_skin = 1;
        $player->tid_cape = 2;
        $player->save();
        $player->refresh();

        // rejected
        $filter = Fakes\Filter::fake();
        $filter->add('can_clear_texture', function ($can, $p, $type) use ($player) {
            $this->assertTrue($player->is($p));
            $this->assertEquals('skin', $type);

            return new Rejection('rejected');
        });
        $this->actingAs($user)
            ->deleteJson(
                route('user.player.clear', ['player' => $player]),
                ['skin' => true]
            )
            ->assertJson(['code' => 1, 'message' => 'rejected']);
        $filter->remove('can_clear_texture');

        // success
        $this->deleteJson(route('user.player.clear', ['player' => $player]), [
                'skin' => true,
                'cape' => true,
                'nope' => true, // invalid texture type is acceptable
            ])->assertJson([
                'code' => 0,
                'message' => trans('user.player.clear.success', ['name' => $player->name]),
            ]);
        $this->assertEquals(0, Player::find($player->pid)->tid_skin);
        $this->assertEquals(0, Player::find($player->pid)->tid_cape);
        Event::assertDispatched(Events\PlayerProfileUpdated::class);

        Event::fake();
        $this->deleteJson(
            route('user.player.clear', ['player' => $player]),
            ['type' => ['skin']]
        )->assertJson(['code' => 0]);
        Event::assertDispatched('player.texture.resetting', function ($event, $payload) use ($player) {
            $this->assertEquals($player->pid, $payload[0]->pid);
            $this->assertEquals('skin', $payload[1]);

            return true;
        });
        Event::assertDispatched('player.texture.reset', function ($event, $payload) use ($player) {
            $this->assertEquals($player->pid, $payload[0]->pid);
            $this->assertEquals('skin', $payload[1]);

            return true;
        });

        Event::fake();
        $this->deleteJson(
            route('user.player.clear', ['player' => $player]),
            ['type' => ['cape']]
        )->assertJson(['code' => 0]);
        Event::assertDispatched('player.texture.resetting', function ($event, $payload) use ($player) {
            $this->assertEquals($player->pid, $payload[0]->pid);
            $this->assertEquals('cape', $payload[1]);

            return true;
        });
        Event::assertDispatched('player.texture.reset', function ($event, $payload) use ($player) {
            $this->assertEquals($player->pid, $payload[0]->pid);
            $this->assertEquals('cape', $payload[1]);

            return true;
        });
    }
}
