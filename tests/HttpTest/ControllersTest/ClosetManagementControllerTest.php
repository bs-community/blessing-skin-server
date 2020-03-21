<?php

namespace Tests;

use App\Models\Texture;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ClosetManagementControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(factory(\App\Models\User::class)->states('admin')->create());
    }

    public function testAdd()
    {
        $user = factory(User::class)->create();
        $texture = factory(Texture::class)->create();

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
    }

    public function testRemove()
    {
        $user = factory(User::class)->create();
        $texture = factory(Texture::class)->create();
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
    }
}
