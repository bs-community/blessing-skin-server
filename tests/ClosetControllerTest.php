<?php

namespace Tests;

use App\Models\User;
use App\Models\Closet;
use App\Models\Texture;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ClosetControllerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var User
     */
    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
        $this->actAs($this->user);
    }

    public function testIndex()
    {
        $this->get('/user/closet')->assertViewHas('user');
    }

    public function testGetClosetData()
    {
        $textures = factory(Texture::class, 10)->create();
        $closet = new Closet($this->user->uid);
        $textures->each(function ($texture) use ($closet) {
            $closet->add($texture->tid, $texture->name);
        });
        $closet->save();

        // Use default query parameters
        $this->getJson('/user/closet-data')
            ->assertJsonStructure([
                'category',
                'total_pages',
                'items' => [['tid', 'name', 'type', 'add_at']],
            ]);

        // Responsive
        $result = $this->json('get', '/user/closet-data?perPage=0')->json();
        $this->assertCount(6, $result['items']);
        $result = $this->json('get', '/user/closet-data?perPage=8')->json();
        $this->assertCount(8, $result['items']);
        $result = $this->json('get', '/user/closet-data?perPage=8&page=2')->json();
        $this->assertCount(2, $result['items']);

        // Get capes
        $cape = factory(Texture::class, 'cape')->create();
        $closet->add($cape->tid, 'custom_name');
        $closet->save();
        $this->getJson('/user/closet-data?category=cape')
            ->assertJson([
                'category' => 'cape',
                'total_pages' => 1,
                'items' => [[
                    'tid' => $cape->tid,
                    'name' => 'custom_name',
                    'type' => 'cape',
                    'add_at' => $closet->get($cape->tid)['add_at'],
                ]],
            ]);

        // Search by keyword
        $random = $textures->random();
        $this->getJson('/user/closet-data?q='.$random->name)
            ->assertJson([
                'category' => 'skin',
                'total_pages' => 1,
                'items' => [[
                    'tid' => $random->tid,
                    'name' => $random->name,
                    'type' => $random->type,
                    'add_at' => $closet->get($random->tid)['add_at'],
                ]],
            ]);
    }

    public function testAdd()
    {
        $texture = factory(Texture::class)->create();
        $name = 'my';
        option(['score_per_closet_item' => 10]);

        // Missing `tid` field
        $this->postJson('/user/closet/add')
            ->assertJson([
                'errno' => 1,
                'msg' => trans('validation.required', ['attribute' => 'tid']),
            ]);

        // `tid` is not a integer
        $this->postJson(
            '/user/closet/add',
            ['tid' => 'string']
        )->assertJson([
            'errno' => 1,
            'msg' => trans('validation.integer', ['attribute' => 'tid']),
        ]);

        // Missing `name` field
        $this->postJson(
            '/user/closet/add',
            ['tid' => 0]
        )->assertJson([
            'errno' => 1,
            'msg' => trans('validation.required', ['attribute' => 'name']),
        ]);

        // `name` field has special characters
        $this->postJson(
            '/user/closet/add',
            ['tid' => 0, 'name' => '\\']
        )->assertJson([
            'errno' => 1,
            'msg' => trans('validation.no_special_chars', ['attribute' => 'name']),
        ]);

        // The user doesn't have enough score to add a texture
        $this->user->setScore(0);
        $this->postJson(
            '/user/closet/add',
            ['tid' => $texture->tid, 'name' => $name]
        )->assertJson([
            'errno' => 7,
            'msg' => trans('user.closet.add.lack-score'),
        ]);

        // Add a not-existed texture
        $this->user->setScore(100);
        $this->postJson(
            '/user/closet/add',
            ['tid' => -1, 'name' => 'my']
        )->assertJson([
            'errno' => 1,
            'msg' => trans('user.closet.add.not-found'),
        ]);

        // Add a texture successfully
        $this->postJson(
            '/user/closet/add',
            ['tid' => $texture->tid, 'name' => $name]
        )->assertJson([
            'errno' => 0,
            'msg' => trans('user.closet.add.success', ['name' => $name]),
        ]);
        $this->assertEquals($texture->likes + 1, Texture::find($texture->tid)->likes);
        $this->user = User::find($this->user->uid);
        $this->assertEquals(90, $this->user->score);
        $closet = new Closet($this->user->uid);
        $this->assertTrue($closet->has($texture->tid));

        // If the texture is duplicated, should be warned
        $this->postJson(
            '/user/closet/add',
            ['tid' => $texture->tid, 'name' => $name]
        )->assertJson([
            'errno' => 1,
            'msg' => trans('user.closet.add.repeated'),
        ]);
    }

    public function testRename()
    {
        $texture = factory(Texture::class)->create();
        $name = 'new';

        // Missing `tid` field
        $this->postJson('/user/closet/rename')
            ->assertJson([
                'errno' => 1,
                'msg' => trans('validation.required', ['attribute' => 'tid']),
            ]);

        // `tid` is not a integer
        $this->postJson(
            '/user/closet/rename',
            ['tid' => 'string']
        )->assertJson([
            'errno' => 1,
            'msg' => trans('validation.integer', ['attribute' => 'tid']),
        ]);

        // Missing `new_name` field
        $this->postJson(
            '/user/closet/rename',
            ['tid' => 0]
        )->assertJson([
            'errno' => 1,
            'msg' => trans('validation.required', ['attribute' => 'new name']),
        ]);

        // `new_name` field has special characters
        $this->postJson(
            '/user/closet/rename',
            ['tid' => 0, 'new_name' => '\\']
        )->assertJson([
            'errno' => 1,
            'msg' => trans('validation.no_special_chars', ['attribute' => 'new name']),
        ]);

        // Rename a not-existed texture
        $this->postJson(
            '/user/closet/rename',
            ['tid' => -1, 'new_name' => $name]
        )->assertJson([
            'errno' => 1,
            'msg' => trans('user.closet.remove.non-existent'),
        ]);

        // Rename a closet item successfully
        $closet = new Closet($this->user->uid);
        $closet->add($texture->tid, 'name');
        $closet->save();
        $closet = new Closet($this->user->uid);
        $this->postJson(
            '/user/closet/rename',
            ['tid' => $texture->tid, 'new_name' => $name]
        )->assertJson([
            'errno' => 0,
            'msg' => trans('user.closet.rename.success', ['name' => 'new']),
        ]);
        $closet->save();
        $closet = new Closet($this->user->uid);
        $this->assertFalse(collect($closet->getItems())->where('name', 'new')->isEmpty());
    }

    public function testRemove()
    {
        $texture = factory(Texture::class)->create();

        // Missing `tid` field
        $this->postJson('/user/closet/remove')
            ->assertJson([
                'errno' => 1,
                'msg' => trans('validation.required', ['attribute' => 'tid']),
            ]);

        // `tid` is not a integer
        $this->postJson(
            '/user/closet/remove',
            ['tid' => 'string']
        )->assertJson([
            'errno' => 1,
            'msg' => trans('validation.integer', ['attribute' => 'tid']),
        ]);

        // Rename a not-existed texture
        $this->postJson(
            '/user/closet/remove',
            ['tid' => -1]
        )->assertJson([
            'errno' => 1,
            'msg' => trans('user.closet.remove.non-existent'),
        ]);

        // Should return score if `return_score` is true
        $closet = new Closet($this->user->uid);
        $closet->add($texture->tid, 'name');
        $closet->save();
        $score = $this->user->score;
        $this->postJson(
            '/user/closet/remove',
            ['tid' => $texture->tid]
        )->assertJson([
            'errno' => 0,
            'msg' => trans('user.closet.remove.success'),
        ]);
        $closet = new Closet($this->user->uid);
        $this->assertEquals($texture->likes - 1, Texture::find($texture->tid)->likes);
        $this->assertEquals($score + option('score_per_closet_item'), $this->user->score);
        $this->assertFalse($closet->has($texture->tid));

        $texture = Texture::find($texture->tid);
        // Should not return score if `return_score` is false
        option(['return_score' => false]);
        $closet = new Closet($this->user->uid);
        $closet->add($texture->tid, 'name');
        $closet->save();
        $score = $this->user->score;
        $this->postJson(
            '/user/closet/remove',
            ['tid' => $texture->tid]
        )->assertJson([
            'errno' => 0,
            'msg' => trans('user.closet.remove.success'),
        ]);
        $closet = new Closet($this->user->uid);
        $this->assertEquals($texture->likes - 1, Texture::find($texture->tid)->likes);
        $this->assertEquals($score, $this->user->score);
        $this->assertFalse($closet->has($texture->tid));
    }
}
