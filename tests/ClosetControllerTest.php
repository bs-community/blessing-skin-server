<?php

use App\Models\User;
use App\Models\Closet;
use App\Models\Texture;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ClosetControllerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var User
     */
    private $user;

    protected function setUp()
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
        return $this->actAs($this->user);
    }

    public function testIndex()
    {
        $this->visit('/user/closet')->assertViewHas('user');
    }

    public function testGetClosetData()
    {
        $textures = factory(Texture::class, 5)->create();
        $closet = new Closet($this->user->uid);
        $textures->each(function ($texture) use ($closet) {
            $closet->add($texture->tid, $texture->name);
        });
        $closet->save();

        // Use default query parameters
        $this->get('/user/closet-data')
            ->seeJsonStructure([
                'category',
                'total_pages',
                'items' => [['tid', 'name', 'type', 'add_at']]
            ]);

        // Get capes
        $cape = factory(Texture::class, 'cape')->create();
        $closet->add($cape->tid, 'custom_name');
        $closet->save();
        $this->get('/user/closet-data?category=cape')
            ->seeJson([
                'category' => 'cape',
                'total_pages' => 1,
                'items' => [[
                    'tid' => $cape->tid,
                    'name' => 'custom_name',
                    'type' => 'cape',
                    'add_at' => $closet->get($cape->tid)['add_at']
                ]]
            ]);

        // Search by keyword
        $random = $textures->random();
        $this->get('/user/closet-data?q='.$random->name)
            ->seeJson([
                'category' => 'skin',
                'total_pages' => 1,
                'items' => [[
                    'tid' => $random->tid,
                    'name' => $random->name,
                    'type' => $random->type,
                    'add_at' => $closet->get($random->tid)['add_at']
                ]]
            ]);
    }

    public function testAdd()
    {
        $texture = factory(Texture::class)->create();
        $name = 'my';
        option(['score_per_closet_item' => 10]);

        // Missing `tid` field
        $this->post('/user/closet/add', [], ['X-Requested-With' => 'XMLHttpRequest'])
            ->seeJson([
                'errno' => 1,
                'msg' => trans('validation.required', ['attribute' => 'tid'])
            ]);

        // `tid` is not a integer
        $this->post(
            '/user/closet/add',
            ['tid' => 'string'],
            ['X-Requested-With' => 'XMLHttpRequest']
        )->seeJson([
            'errno' => 1,
            'msg' => trans('validation.integer', ['attribute' => 'tid'])
        ]);

        // Missing `name` field
        $this->post(
            '/user/closet/add',
            ['tid' => 0],
            ['X-Requested-With' => 'XMLHttpRequest']
        )->seeJson([
            'errno' => 1,
            'msg' => trans('validation.required', ['attribute' => 'Name'])
        ]);

        // `name` field has special characters
        $this->post(
            '/user/closet/add',
            ['tid' => 0, 'name' => '\\'],
            ['X-Requested-With' => 'XMLHttpRequest']
        )->seeJson([
            'errno' => 1,
            'msg' => trans('validation.no_special_chars', ['attribute' => 'Name'])
        ]);

        // The user doesn't have enough score to add a texture
        $this->user->setScore(0);
        $this->post(
            '/user/closet/add',
            ['tid' => $texture->tid, 'name' => $name]
        )->seeJson([
            'errno' => 7,
            'msg' => trans('user.closet.add.lack-score')
        ]);

        // Add a not-existed texture
        $this->user->setScore(100);
        $this->post(
            '/user/closet/add',
            ['tid' => -1, 'name' => 'my']
        )->seeJson([
            'errno' => 1,
            'msg' => trans('user.closet.add.not-found')
        ]);

        // Add a texture successfully
        $this->post(
            '/user/closet/add',
            ['tid' => $texture->tid, 'name' => $name]
        )->seeJson([
            'errno' => 0,
            'msg' => trans('user.closet.add.success', ['name' => $name])
        ]);
        $this->assertEquals($texture->likes + 1, Texture::find($texture->tid)->likes);
        $this->user = User::find($this->user->uid);
        $this->assertEquals(90, $this->user->score);
        $closet = new Closet($this->user->uid);
        $this->assertTrue($closet->has($texture->tid));

        // If the texture is duplicated, should be warned
        $this->post(
            '/user/closet/add',
            ['tid' => $texture->tid, 'name' => $name]
        )->seeJson([
            'errno' => 1,
            'msg' => trans('user.closet.add.repeated')
        ]);
    }

    public function testRename()
    {
        $texture = factory(Texture::class)->create();
        $name = 'new';

        // Missing `tid` field
        $this->post('/user/closet/rename', [], ['X-Requested-With' => 'XMLHttpRequest'])
            ->seeJson([
                'errno' => 1,
                'msg' => trans('validation.required', ['attribute' => 'tid'])
            ]);

        // `tid` is not a integer
        $this->post(
            '/user/closet/rename',
            ['tid' => 'string'],
            ['X-Requested-With' => 'XMLHttpRequest']
        )->seeJson([
            'errno' => 1,
            'msg' => trans('validation.integer', ['attribute' => 'tid'])
        ]);

        // Missing `new_name` field
        $this->post(
            '/user/closet/rename',
            ['tid' => 0],
            ['X-Requested-With' => 'XMLHttpRequest']
        )->seeJson([
            'errno' => 1,
            'msg' => trans('validation.required', ['attribute' => 'new name'])
        ]);

        // `new_name` field has special characters
        $this->post(
            '/user/closet/rename',
            ['tid' => 0, 'new_name' => '\\'],
            ['X-Requested-With' => 'XMLHttpRequest']
        )->seeJson([
            'errno' => 1,
            'msg' => trans('validation.no_special_chars', ['attribute' => 'new name'])
        ]);

        // Rename a not-existed texture
        $this->post(
            '/user/closet/rename',
            ['tid' => -1, 'new_name' => $name]
        )->seeJson([
            'errno' => 1,
            'msg' => trans('user.closet.remove.non-existent')
        ]);

        // Rename a closet item successfully
        $closet = new Closet($this->user->uid);
        $closet->add($texture->tid, 'name');
        $closet->save();
        $closet = new Closet($this->user->uid);
        $this->post(
            '/user/closet/rename',
            ['tid' => $texture->tid, 'new_name' => $name]
        )->seeJson([
            'errno' => 0,
            'msg' => trans('user.closet.rename.success', ['name' => 'new'])
        ]);
        $closet->save();
        $closet = new Closet($this->user->uid);
        $this->assertFalse(collect($closet->getItems())->where('name', 'new')->isEmpty());
    }

    public function testRemove()
    {
        $texture = factory(Texture::class)->create();

        // Missing `tid` field
        $this->post('/user/closet/remove', [], ['X-Requested-With' => 'XMLHttpRequest'])
            ->seeJson([
                'errno' => 1,
                'msg' => trans('validation.required', ['attribute' => 'tid'])
            ]);

        // `tid` is not a integer
        $this->post(
            '/user/closet/remove',
            ['tid' => 'string'],
            ['X-Requested-With' => 'XMLHttpRequest']
        )->seeJson([
            'errno' => 1,
            'msg' => trans('validation.integer', ['attribute' => 'tid'])
        ]);

        // Rename a not-existed texture
        $this->post(
            '/user/closet/remove',
            ['tid' => -1]
        )->seeJson([
            'errno' => 1,
            'msg' => trans('user.closet.remove.non-existent')
        ]);

        // Should return score if `return_score` is true
        $closet = new Closet($this->user->uid);
        $closet->add($texture->tid, 'name');
        $closet->save();
        $score = $this->user->score;
        $this->post(
            '/user/closet/remove',
            ['tid' => $texture->tid]
        )->seeJson([
            'errno' => 0,
            'msg' => trans('user.closet.remove.success')
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
        $this->post(
            '/user/closet/remove',
            ['tid' => $texture->tid]
        )->seeJson([
            'errno' => 0,
            'msg' => trans('user.closet.remove.success')
        ]);
        $closet = new Closet($this->user->uid);
        $this->assertEquals($texture->likes - 1, Texture::find($texture->tid)->likes);
        $this->assertEquals($score, $this->user->score);
        $this->assertFalse($closet->has($texture->tid));
    }
}
