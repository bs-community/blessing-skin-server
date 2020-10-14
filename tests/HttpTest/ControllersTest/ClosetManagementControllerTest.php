<?php

namespace Tests;

use App\Models\Texture;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;

class ClosetManagementControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->admin()->create());
    }

    public function testList()
    {
        $texture = Texture::factory()->create();
        $admin = User::factory()->admin()->create();
        $admin->closet()->attach($texture->tid);

        $this->actingAs($admin, 'oauth')
            ->getJson('/api/admin/closet/'.$admin->uid)
            ->assertJson([['tid' => $texture->tid]]);
    }

    public function testAdd()
    {
        Event::fake();
        $user = User::factory()->create();
        $texture = Texture::factory()->create();

        $this->postJson('/admin/closet/'.$user->uid, ['tid' => $texture->tid])
            ->assertJson([
                'code' => 0,
                'data' => [
                    'user' => $user->toArray(),
                    'texture' => $texture->toArray(),
                ],
            ]);
        $item = $user->closet()->first();
        $this->assertEquals($texture->tid, $item->tid);
        $this->assertEquals($texture->name, $item->pivot->item_name);
        Event::assertDispatched(
            'closet.adding',
            function ($eventName, $payload) use ($texture, $user) {
                $this->assertEquals($texture->tid, $payload[0]);
                $this->assertEquals($texture->name, $payload[1]);
                $this->assertTrue($user->is($payload[2]));

                return true;
            }
        );
        Event::assertDispatched(
            'closet.added',
            function ($eventName, $payload) use ($texture, $user) {
                $this->assertTrue($texture->is($payload[0]));
                $this->assertEquals($texture->name, $payload[1]);
                $this->assertTrue($user->is($payload[2]));

                return true;
            }
        );
    }

    public function testRemove()
    {
        Event::fake();
        $user = User::factory()->create();
        $texture = Texture::factory()->create();
        $user->closet()->attach($texture->tid, ['item_name' => '']);

        $this->deleteJson('/admin/closet/'.$user->uid, ['tid' => $texture->tid])
            ->assertJson([
                'code' => 0,
                'data' => [
                    'user' => $user->toArray(),
                    'texture' => $texture->toArray(),
                ],
            ]);
        $this->assertCount(0, $user->closet);
        Event::assertDispatched(
            'closet.removing',
            function ($eventName, $payload) use ($texture, $user) {
                $this->assertEquals($texture->tid, $payload[0]);
                $this->assertTrue($user->is($payload[1]));

                return true;
            }
        );
        Event::assertDispatched(
            'closet.removed',
            function ($eventName, $payload) use ($texture, $user) {
                $this->assertTrue($texture->is($payload[0]));
                $this->assertTrue($user->is($payload[1]));

                return true;
            }
        );
    }
}
