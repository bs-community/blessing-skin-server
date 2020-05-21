<?php

namespace Tests;

use App\Models\Player;
use App\Models\Texture;
use App\Models\User;
use Blessing\Minecraft;
use Cache;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Image;

class TextureControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function testJson()
    {
        $steve = factory(Texture::class)->create();

        // Player is not existed
        $this->get('/nope.json')->assertStatus(404);

        // Player is banned
        $player = factory(Player::class)->create(['tid_skin' => $steve->tid]);
        $player->user->permission = User::BANNED;
        $player->user->save();
        $this->get("/{$player->name}.json")
            ->assertSee(trans('general.player-banned'))
            ->assertStatus(403);

        $player->user->permission = User::NORMAL;
        $player->user->save();
        $this->getJson("/{$player->name}.json")
            ->assertJson([
                'username' => $player->name,
                'skins' => [
                    'default' => $steve->hash,
                ],
                'cape' => null,
            ])->assertHeader('Last-Modified');
    }

    public function testPreview()
    {
        $disk = Storage::fake('textures');

        $this->get('/preview/0')->assertNotFound();

        $this->mock(Minecraft::class, function ($mock) {
            $mock->shouldReceive('renderSkin')->andReturn(Image::canvas(1, 1));
            $mock->shouldReceive('renderCape')->andReturn(Image::canvas(1, 1));
        });

        $skin = factory(Texture::class)->create();
        $this->get('/preview/'.$skin->tid)->assertNotFound();

        $disk->put($skin->hash, '');
        $this->get('/preview/'.$skin->tid)
            ->assertHeader('Content-Type', 'image/webp');
        Cache::clear();
        $this->get('/preview/'.$skin->tid.'?png')
            ->assertHeader('Content-Type', 'image/png');
        $this->assertTrue(Cache::has('preview-t'.$skin->tid));

        $cape = factory(Texture::class)->states('cape')->create();
        $disk->put($cape->hash, '');
        $this->get('/preview/'.$cape->tid.'?height=100')->assertHeader('Content-Type', 'image/webp');
        $this->assertTrue(Cache::has('preview-t'.$cape->tid));
    }

    public function testRaw()
    {
        $disk = Storage::fake('textures');
        $skin = factory(Texture::class)->create();

        // Not found
        $this->get('/raw/0')->assertNotFound();

        // Missing texture file
        $this->get('/raw/'.$skin->tid)->assertNotFound();

        // Success
        $disk->put($skin->hash, '');
        $this->get('/raw/'.$skin->tid)->assertHeader('Content-Type', 'image/png');

        // Disallow downloading texture directly
        option(['allow_downloading_texture' => false]);
        $this->get('/raw/'.$skin->tid)->assertForbidden();
    }

    public function testTexture()
    {
        $disk = Storage::fake('textures');
        $skin = factory(Texture::class)->create();

        $this->get('/textures/'.$skin->hash)->assertNotFound();

        $disk->put($skin->hash, '');
        $this->get('/textures/'.$skin->hash)
            ->assertHeader('Content-Type', 'image/png');
    }

    public function testAvatarByPlayer()
    {
        $disk = Storage::fake('textures');

        $this->get('/avatar/player/abc')->assertNotFound();

        $player = factory(Player::class)->create();
        $this->get('/avatar/player/'.$player->name)
            ->assertSuccessful()
            ->assertHeader('Content-Type', 'image/webp');

        $this->mock(Minecraft::class, function ($mock) {
            $mock->shouldReceive('render2dAvatar')->andReturn(Image::canvas(1, 1));
        });
        $texture = factory(Texture::class)->create();
        $disk->put($texture->hash, '');
        $player->tid_skin = $texture->tid;
        $player->save();
        $this->get('/avatar/player/'.$player->name)
            ->assertSuccessful()
            ->assertHeader('Content-Type', 'image/webp');

        Cache::clear();
        $image = $this->get('/avatar/player/'.$player->name.'?png')
            ->assertSuccessful()
            ->assertHeader('Content-Type', 'image/png')
            ->getContent();
        $image = Image::make($image);
        $this->assertEquals(100, $image->width());
        $this->assertEquals(100, $image->height());

        $image = $this->get('/avatar/player/'.$player->name.'?size=50&png')->getContent();
        $image = Image::make($image);
        $this->assertEquals(50, $image->width());
        $this->assertEquals(50, $image->height());
    }

    public function testAvatarByUser()
    {
        $disk = Storage::fake('textures');

        $this->get('/avatar/user/0')
            ->assertSuccessful()
            ->assertHeader('Content-Type', 'image/webp');

        $user = factory(User::class)->create();
        $this->get('/avatar/user/'.$user->uid)
            ->assertSuccessful()
            ->assertHeader('Content-Type', 'image/webp');

        $this->mock(Minecraft::class, function ($mock) {
            $mock->shouldReceive('render2dAvatar')->andReturn(Image::canvas(1, 1));
        });
        $texture = factory(Texture::class)->create();
        $disk->put($texture->hash, '');
        $user->avatar = $texture->tid;
        $user->save();
        $this->get('/avatar/user/'.$user->uid)
            ->assertSuccessful()
            ->assertHeader('Content-Type', 'image/webp');

        Cache::clear();
        $image = $this->get('/avatar/user/'.$user->uid.'?png')
            ->assertSuccessful()
            ->assertHeader('Content-Type', 'image/png')
            ->getContent();
        $image = Image::make($image);
        $this->assertEquals(100, $image->width());
        $this->assertEquals(100, $image->height());

        $image = $this->get('/avatar/user/'.$user->uid.'?size=50&png')->getContent();
        $image = Image::make($image);
        $this->assertEquals(50, $image->width());
        $this->assertEquals(50, $image->height());
    }

    public function testAvatarByTexture()
    {
        $disk = Storage::fake('textures');

        $this->get('/avatar/0')
            ->assertSuccessful()
            ->assertHeader('Content-Type', 'image/webp');
        Cache::clear();
        $image = $this->get('/avatar/0?png')
            ->assertSuccessful()
            ->assertHeader('Content-Type', 'image/png')
            ->getContent();
        $image = Image::make($image);
        $this->assertEquals(100, $image->width());
        $this->assertEquals(100, $image->height());

        $texture = factory(Texture::class)->create();
        $this->get('/avatar/'.$texture->tid)
            ->assertSuccessful()
            ->assertHeader('Content-Type', 'image/webp');

        $this->mock(Minecraft::class, function ($mock) {
            $mock->shouldReceive('render2dAvatar')->andReturn(Image::canvas(1, 1));
            $mock->shouldReceive('render3dAvatar')->andReturn(Image::canvas(1, 1));
        });
        $disk->put($texture->hash, '');
        $this->get('/avatar/'.$texture->tid)
            ->assertSuccessful()
            ->assertHeader('Content-Type', 'image/webp');

        Cache::clear();
        $this->get('/avatar/'.$texture->tid.'?png')
            ->assertSuccessful()
            ->assertHeader('Content-Type', 'image/png');
        $image = Image::make($image);
        $this->assertEquals(100, $image->width());
        $this->assertEquals(100, $image->height());
        $this->assertTrue(Cache::has('avatar-2d-t'.$texture->tid.'-s100'));

        $image = $this->get('/avatar/'.$texture->tid.'?size=50&png')->getContent();
        $image = Image::make($image);
        $this->assertEquals(50, $image->width());
        $this->assertEquals(50, $image->height());
        $this->assertTrue(Cache::has('avatar-2d-t'.$texture->tid.'-s50'));

        $this->get('/avatar/'.$texture->tid.'?3d')
            ->assertSuccessful()
            ->assertHeader('Content-Type', 'image/webp');
        $this->assertTrue(Cache::has('avatar-3d-t'.$texture->tid.'-s100'));
    }
}
