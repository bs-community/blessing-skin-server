<?php

namespace Tests;

use App\Models\Player;
use App\Models\Texture;
use App\Models\User;
use Event;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PlayersManagementControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->admin()->create());
    }

    public function testList()
    {
        $player = Player::factory()->create();

        $this->getJson(route('admin.players.list'))
            ->assertJson(['data' => [$player->toArray()]]);
    }

    public function testAccessControl()
    {
        // an admin can't operate another admin's player
        $admin = User::factory()->admin()->create();
        /** @var Player */
        $player = Player::factory()->create(['uid' => $admin->uid]);
        $this->putJson(
            route('admin.players.name', ['player' => $player->pid]),
            ['player_name' => 'abcd']
        )->assertJson([
            'code' => 1,
            'message' => trans('admin.players.no-permission'),
        ])->assertForbidden();

        // for self is OK
        $this->actingAs($admin)
            ->putJson(
                route('admin.players.name', ['player' => $player->pid]),
                ['player_name' => 'abcd']
            )->assertJson(['code' => 0]);

        // super admin
        $superAdmin = User::factory()->superAdmin()->create();
        /** @var Player */
        $player = Player::factory()->create(['uid' => $superAdmin->uid]);
        $this->putJson(
            route('admin.players.name', ['player' => $player->pid]),
            ['player_name' => 'abcd']
        )->assertJson([
            'code' => 1,
            'message' => trans('admin.players.no-permission'),
        ])->assertForbidden();
    }

    public function testName()
    {
        /** @var Player */
        $player = Player::factory()->create();

        // missing `player_name` field
        $this->putJson(
            route('admin.players.name', ['player' => $player->pid])
        )->assertJsonValidationErrors(['player_name']);

        // duplicated player name
        $this->putJson(
            route('admin.players.name', ['player' => $player->pid]),
            ['player_name' => $player->name]
        )->assertJsonValidationErrors(['player_name']);

        // rename a player successfully
        Event::fake();
        $this->putJson(
            route('admin.players.name', ['player' => $player->pid]),
            ['player_name' => 'new_name']
        )->assertJson([
            'code' => 0,
            'message' => trans('admin.players.name.success', ['player' => 'new_name']),
        ]);
        $oldName = $player->name;
        $player->refresh();
        $this->assertEquals('new_name', $player->name);
        Event::assertDispatched(
            'player.renaming',
            function ($eventName, $payload) use ($player) {
                $this->assertEquals($player->pid, $payload[0]->pid);
                $this->assertEquals('new_name', $payload[1]);

                return true;
            }
        );
        Event::assertDispatched(
            'player.renamed',
            function ($eventName, $payload) use ($player, $oldName) {
                $this->assertEquals($player->pid, $payload[0]->pid);
                $this->assertEquals($oldName, $payload[1]);

                return true;
            }
        );
    }

    public function testOwner()
    {
        Event::fake();

        /** @var Player */
        $player = Player::factory()->create();

        // missing `uid` field
        $this->putJson(route('admin.players.owner', ['player' => $player->pid]))
            ->assertJsonValidationErrors(['uid']);

        // with a non-integer `uid` value
        $this->putJson(
            route('admin.players.owner', ['player' => $player->pid]),
            ['uid' => 's']
        )->assertJsonValidationErrors(['uid']);

        // change owner to a not-existed user
        $this->putJson(
            route('admin.players.owner', ['player' => $player->pid]),
            ['uid' => -1]
        )->assertJson([
            'code' => 1,
            'message' => trans('admin.users.operations.non-existent'),
        ]);
        Event::assertDispatched(
            'player.owner.updating',
            function ($eventName, $payload) use ($player) {
                $this->assertEquals($player->pid, $payload[0]->pid);
                $this->assertEquals(-1, $payload[1]);

                return true;
            }
        );
        Event::assertNotDispatched('player.owner.updated');

        // change owner successfully
        Event::fake();
        /** @var User */
        $user = User::factory()->create();
        $this->putJson(
            route('admin.players.owner', ['player' => $player->pid]),
            ['uid' => $user->uid]
        )->assertJson([
            'code' => 0,
            'message' => trans(
                'admin.players.owner.success',
                ['player' => $player->name, 'user' => $user->nickname]
            ),
        ]);
        Event::assertDispatched(
            'player.owner.updating',
            function ($eventName, $payload) use ($player, $user) {
                $this->assertEquals($player->pid, $payload[0]->pid);
                $this->assertEquals($user->uid, $payload[1]);

                return true;
            }
        );
        Event::assertDispatched(
            'player.owner.updated',
            function ($eventName, $payload) use ($player, $user) {
                $this->assertEquals($player->pid, $payload[0]->pid);
                $this->assertEquals($user->uid, $payload[1]->uid);

                return true;
            }
        );
    }

    public function testTexture()
    {
        Event::fake();

        /** @var Player */
        $player = Player::factory()->create();

        // missing `tid` field
        $this->putJson(
            route('admin.players.texture', ['player' => $player->pid])
        )->assertJsonValidationErrors(['tid']);

        // change texture with a non-integer value
        $this->putJson(
            route('admin.players.texture', ['player' => $player->pid]),
            ['tid' => 's']
        )->assertJsonValidationErrors(['tid']);

        // missing `type` field
        $this->putJson(
            route('admin.players.texture', ['player' => $player->pid]),
            ['tid' => -1]
        )->assertJsonValidationErrors(['type']);

        // invalid type
        $this->putJson(
            route('admin.players.texture', ['player' => $player->pid]),
            ['tid' => -1, 'type' => 'elytra']
        )->assertJsonValidationErrors(['type']);

        // invalid texture
        $this->putJson(
            route('admin.players.texture', ['player' => $player->pid]),
            ['tid' => -1, 'type' => 'skin']
        )->assertJson([
            'code' => 1,
            'message' => trans('admin.players.textures.non-existent', ['tid' => -1]),
        ]);
        Event::assertDispatched(
            'player.texture.updating',
            function ($eventName, $payload) use ($player) {
                $this->assertEquals($player->pid, $payload[0]->pid);
                $this->assertEquals('skin', $payload[1]);
                $this->assertEquals(-1, $payload[2]);

                return true;
            }
        );
        Event::assertNotDispatched('player.texture.updated');

        /** @var Texture */
        $skin = Texture::factory()->create();
        /** @var Texture */
        $cape = Texture::factory()->cape()->create();

        // skin
        Event::fake();
        $this->putJson(
            route('admin.players.texture', ['player' => $player->pid]),
            ['tid' => $skin->tid, 'type' => 'skin']
        )->assertJson([
            'code' => 0,
            'message' => trans('admin.players.textures.success', ['player' => $player->name]),
        ]);
        $previousTid = $player->tid_skin;
        $player->refresh();
        $this->assertEquals($skin->tid, $player->tid_skin);
        Event::assertDispatched(
            'player.texture.updating',
            function ($eventName, $payload) use ($player, $skin) {
                $this->assertEquals($player->pid, $payload[0]->pid);
                $this->assertEquals('skin', $payload[1]);
                $this->assertEquals($skin->tid, $payload[2]);

                return true;
            }
        );
        Event::assertDispatched(
            'player.texture.updated',
            function ($eventName, $payload) use ($player, $previousTid) {
                $this->assertEquals($player->pid, $payload[0]->pid);
                $this->assertEquals('skin', $payload[1]);
                $this->assertEquals($previousTid, $payload[2]);

                return true;
            }
        );

        // cape
        Event::fake();
        $this->putJson(
            route('admin.players.texture', ['player' => $player->pid]),
            ['tid' => $cape->tid, 'type' => 'cape']
        )->assertJson([
            'code' => 0,
            'message' => trans('admin.players.textures.success', ['player' => $player->name]),
        ]);
        $previousTid = $player->tid_cape;
        $player->refresh();
        $this->assertEquals($cape->tid, $player->tid_cape);
        Event::assertDispatched(
            'player.texture.updating',
            function ($eventName, $payload) use ($player, $cape) {
                $this->assertEquals($player->pid, $payload[0]->pid);
                $this->assertEquals('cape', $payload[1]);
                $this->assertEquals($cape->tid, $payload[2]);

                return true;
            }
        );
        Event::assertDispatched(
            'player.texture.updated',
            function ($eventName, $payload) use ($player, $previousTid) {
                $this->assertEquals($player->pid, $payload[0]->pid);
                $this->assertEquals('cape', $payload[1]);
                $this->assertEquals($previousTid, $payload[2]);

                return true;
            }
        );

        // reset texture
        Event::fake();
        $this->putJson(
            route('admin.players.texture', ['player' => $player->pid]),
            ['tid' => 0, 'type' => 'skin']
        )->assertJson([
            'code' => 0,
            'message' => trans('admin.players.textures.success', ['player' => $player->name]),
        ]);
        $previousTid = $player->tid_skin;
        $player->refresh();
        $this->assertEquals(0, $player->tid_skin);
        $this->assertNotEquals(0, $player->tid_cape);
        Event::assertDispatched(
            'player.texture.updating',
            function ($eventName, $payload) use ($player) {
                $this->assertEquals($player->pid, $payload[0]->pid);
                $this->assertEquals('skin', $payload[1]);
                $this->assertEquals(0, $payload[2]);

                return true;
            }
        );
        Event::assertDispatched(
            'player.texture.updated',
            function ($eventName, $payload) use ($player, $previousTid) {
                $this->assertEquals($player->pid, $payload[0]->pid);
                $this->assertEquals('skin', $payload[1]);
                $this->assertEquals($previousTid, $payload[2]);

                return true;
            }
        );

        $this->putJson(
            route('admin.players.texture', ['player' => $player->pid]),
            ['tid' => 0, 'type' => 'cape']
        )->assertJson([
            'code' => 0,
            'message' => trans('admin.players.textures.success', ['player' => $player->name]),
        ]);
        $player->refresh();
        $this->assertEquals(0, $player->tid_cape);
    }

    public function testDelete()
    {
        Event::fake();

        /** @var Player */
        $player = Player::factory()->create();

        $this->deleteJson(route('admin.players.delete', ['player' => $player->pid]))
            ->assertJson([
                'code' => 0,
                'message' => trans('admin.players.delete.success'),
            ]);
        Event::assertDispatched('player.deleting', function ($eventName, $payload) use ($player) {
            $this->assertEquals($player->pid, $payload[0]->pid);

            return true;
        });
        Event::assertDispatched('player.deleted', function ($eventName, $payload) use ($player) {
            $this->assertEquals($player->pid, $payload[0]->pid);

            return true;
        });
        $this->assertNull(Player::find($player->pid));
    }
}
