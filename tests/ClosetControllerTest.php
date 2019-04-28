<?php

namespace Tests;

use App\Models\User;
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
        $this->actingAs($this->user);
    }

    public function testIndex()
    {
        $this->get('/user/closet')->assertViewIs('user.closet');
    }

    public function testGetClosetData()
    {
        $textures = factory(Texture::class, 10)->create();
        $textures->each(function ($t) {
            $this->user->closet()->attach($t->tid, ['item_name' => $t->name]);
        });

        // Use default query parameters
        $this->getJson('/user/closet-data')
            ->assertJsonStructure([
                'data' => [
                    'category',
                    'total_pages',
                    'items' => [['tid', 'name', 'type']],
                ]
            ]);

        // Responsive
        $result = $this->json('get', '/user/closet-data?perPage=0')->json()['data'];
        $this->assertCount(6, $result['items']);
        $result = $this->json('get', '/user/closet-data?perPage=8')->json()['data'];
        $this->assertCount(8, $result['items']);
        $result = $this->json('get', '/user/closet-data?perPage=8&page=2')->json()['data'];
        $this->assertCount(2, $result['items']);

        // Get capes
        $cape = factory(Texture::class, 'cape')->create();
        $this->user->closet()->attach($cape->tid, ['item_name' => 'custom_name']);
        $this->getJson('/user/closet-data?category=cape')
            ->assertJson(['data' => [
                'category' => 'cape',
                'total_pages' => 1,
                'items' => [[
                    'tid' => $cape->tid,
                    'name' => 'custom_name',
                    'type' => 'cape',
                ]],
            ]]);

        // Search by keyword
        $random = $textures->random();
        $this->getJson('/user/closet-data?q='.$random->name)
            ->assertJson(['data' => [
                'category' => 'skin',
                'total_pages' => 1,
                'items' => [[
                    'tid' => $random->tid,
                    'name' => $random->name,
                    'type' => $random->type,
                ]],
            ]]);
    }

    public function testAdd()
    {
        $uploader = factory(User::class)->create(['score' => 0]);
        $texture = factory(Texture::class)->create(['uploader' => $uploader->uid]);
        $likes = $texture->likes;
        $name = 'my';
        option(['score_per_closet_item' => 10]);

        // Missing `tid` field
        $this->postJson('/user/closet/add')->assertJsonValidationErrors('tid');

        // `tid` is not a integer
        $this->postJson(
            '/user/closet/add',
            ['tid' => 'string']
        )->assertJsonValidationErrors('tid');

        // Missing `name` field
        $this->postJson(
            '/user/closet/add',
            ['tid' => 0]
        )->assertJsonValidationErrors('name');

        // `name` field has special characters
        $this->postJson(
            '/user/closet/add',
            ['tid' => 0, 'name' => '\\']
        )->assertJsonValidationErrors('name');

        // The user doesn't have enough score to add a texture
        $this->user->setScore(0);
        $this->postJson(
            '/user/closet/add',
            ['tid' => $texture->tid, 'name' => $name]
        )->assertJson([
            'code' => 7,
            'message' => trans('user.closet.add.lack-score'),
        ]);

        // Add a not-existed texture
        $this->user->setScore(100);
        $this->postJson(
            '/user/closet/add',
            ['tid' => -1, 'name' => 'my']
        )->assertJson([
            'code' => 1,
            'message' => trans('user.closet.add.not-found'),
        ]);

        // Texture is private
        option(['score_award_per_like' => 5]);
        $privateTexture = factory(Texture::class)->create([
            'public' => false,
            'uploader' => $uploader->uid + 1,
        ]);
        $this->postJson(
            '/user/closet/add',
            ['tid' => $privateTexture->tid, 'name' => $name]
        )->assertJson([
            'code' => 1,
            'message' => trans('skinlib.show.private'),
        ]);

        // Add a texture successfully
        $this->postJson(
            '/user/closet/add',
            ['tid' => $texture->tid, 'name' => $name]
        )->assertJson([
            'code' => 0,
            'message' => trans('user.closet.add.success', ['name' => $name]),
        ]);
        $this->assertEquals($likes + 1, Texture::find($texture->tid)->likes);
        $this->user = User::find($this->user->uid);
        $this->assertEquals(90, $this->user->score);
        $this->assertEquals(1, $this->user->closet()->count());
        $uploader->refresh();
        $this->assertEquals(5, $uploader->score);

        // If the texture is duplicated, should be warned
        $this->postJson(
            '/user/closet/add',
            ['tid' => $texture->tid, 'name' => $name]
        )->assertJson([
            'code' => 1,
            'message' => trans('user.closet.add.repeated'),
        ]);
    }

    public function testRename()
    {
        $texture = factory(Texture::class)->create();
        $name = 'new';

        // Missing `name` field
        $this->postJson('/user/closet/rename/0')->assertJsonValidationErrors('name');

        // `new_name` field has special characters
        $this->postJson('/user/closet/rename/0', ['name' => '\\'])
            ->assertJsonValidationErrors('name');

        // Rename a not-existed texture
        $this->postJson('/user/closet/rename/-1', ['name' => $name])
            ->assertJson([
                'code' => 1,
                'message' => trans('user.closet.remove.non-existent'),
            ]);

        // Rename a closet item successfully
        $this->user->closet()->attach($texture->tid, ['item_name' => 'name']);
        $this->postJson('/user/closet/rename/'.$texture->tid, ['name' => $name])
            ->assertJson([
                'code' => 0,
                'message' => trans('user.closet.rename.success', ['name' => $name]),
            ]);
        $this->assertEquals(1, $this->user->closet()->where('item_name', $name)->count());
    }

    public function testRemove()
    {
        $uploader = factory(User::class)->create(['score' => 5]);
        $texture = factory(Texture::class)->create(['uploader' => $uploader->uid]);
        $likes = $texture->likes;

        // Rename a not-existed texture
        $this->postJson('/user/closet/remove/-1')
            ->assertJson([
                'code' => 1,
                'message' => trans('user.closet.remove.non-existent'),
            ]);

        // Should return score if `return_score` is true
        option(['score_award_per_like' => 5]);
        $this->user->closet()->attach($texture->tid, ['item_name' => 'name']);
        $score = $this->user->score;
        $this->postJson('/user/closet/remove/'.$texture->tid)
            ->assertJson([
                'code' => 0,
                'message' => trans('user.closet.remove.success'),
            ]);
        $this->assertEquals($likes, Texture::find($texture->tid)->likes);
        $this->assertEquals($score + option('score_per_closet_item'), $this->user->score);
        $this->assertEquals(0, $this->user->closet()->count());
        $uploader->refresh();
        $this->assertEquals(0, $uploader->score);

        $texture = Texture::find($texture->tid);
        $likes = $texture->likes;
        // Should not return score if `return_score` is false
        option(['return_score' => false]);
        $this->user->closet()->attach($texture->tid, ['item_name' => 'name']);
        $score = $this->user->score;
        $this->postJson('/user/closet/remove/'.$texture->tid)->assertJson(['code' => 0]);
        $this->assertEquals($likes, Texture::find($texture->tid)->likes);
        $this->assertEquals($score, $this->user->score);
        $this->assertEquals(0, $this->user->closet()->count());
    }
}
