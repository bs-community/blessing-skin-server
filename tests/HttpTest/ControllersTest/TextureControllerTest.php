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
        $steve = Texture::factory()->create();

        // Player is not existed
        $this->get('/nope.json')->assertStatus(404);

        // Player is banned
        $player = Player::factory()->create(['tid_skin' => $steve->tid]);
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

    public function testPreviewByHash()
    {
        $disk = Storage::fake('textures');
        $this->mock(Minecraft::class, function ($mock) {
            $mock->shouldReceive('renderSkin')->andReturn(Image::canvas(1, 1));
            $mock->shouldReceive('renderCape')->andReturn(Image::canvas(1, 1));
        });

        $skin = Texture::factory()->create();
        $disk->put($skin->hash, '');
        $this->get(route('preview.hash', ['hash' => $skin->hash]))
            ->assertHeader('Content-Type', 'image/webp');
    }

    public function testPreview()
    {
        $disk = Storage::fake('textures');

        $this->mock(Minecraft::class, function ($mock) {
            $mock->shouldReceive('renderSkin')->andReturn(Image::canvas(1, 1));
            $mock->shouldReceive('renderCape')->andReturn(Image::canvas(1, 1));
        });

        $skin = Texture::factory()->create();
        $this->get(route('preview.texture', ['texture' => $skin]))->assertNotFound();

        $disk->put($skin->hash, '');
        $this->get(route('preview.texture', ['texture' => $skin]))
            ->assertHeader('Content-Type', 'image/webp');
        Cache::clear();
        $this->get(route('preview.texture', ['texture' => $skin, 'png' => true]))
            ->assertHeader('Content-Type', 'image/png');
        $this->assertTrue(Cache::has('preview-t'.$skin->tid.'-png'));

        $cape = Texture::factory()->cape()->create();
        $disk->put($cape->hash, '');
        $this->get(route('preview.texture', ['texture' => $cape, 'height' => 100]))
            ->assertHeader('Content-Type', 'image/webp');
        $this->assertTrue(Cache::has('preview-t'.$cape->tid.'-webp'));
    }

    public function testRaw()
    {
        $disk = Storage::fake('textures');
        $skin = Texture::factory()->create();

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
        $skin = Texture::factory()->create();

        $this->get('/textures/'.$skin->hash)->assertNotFound();

        $disk->put($skin->hash, '');
        $this->get('/textures/'.$skin->hash)
            ->assertSuccessful()
            ->assertHeader('Content-Type', 'image/png');
        $this->get('/csl/textures/'.$skin->hash)
            ->assertSuccessful()
            ->assertHeader('Content-Type', 'image/png');
    }

    public function testAvatarByPlayer()
    {
        $disk = Storage::fake('textures');

        $this->get(route('avatar.player', ['name' => 'abc']))->assertNotFound();

        $player = Player::factory()->create();
        $this->get(route('avatar.player', ['name' => $player->name]))
            ->assertSuccessful()
            ->assertHeader('Content-Type', 'image/webp');

        $this->mock(Minecraft::class, function ($mock) {
            $mock->shouldReceive('render2dAvatar')->andReturn(Image::canvas(1, 1));
        });
        $texture = Texture::factory()->create();
        $disk->put($texture->hash, '');
        $player->tid_skin = $texture->tid;
        $player->save();
        $this->get(route('avatar.player', ['name' => $player->name]))
            ->assertSuccessful()
            ->assertHeader('Content-Type', 'image/webp');

        Cache::clear();
        $image = $this->get(route('avatar.player', [
            'name' => $player->name,
            'png' => true,
        ]))->assertSuccessful()
            ->assertHeader('Content-Type', 'image/png')
            ->getContent();
        $image = Image::make($image);
        $this->assertEquals(100, $image->width());
        $this->assertEquals(100, $image->height());

        $image = $this->get(route('avatar.player', [
            'name' => $player->name,
            'size' => 50,
            'png' => true,
        ]))->getContent();
        $image = Image::make($image);
        $this->assertEquals(50, $image->width());
        $this->assertEquals(50, $image->height());
    }

    public function testAvatarByUser()
    {
        $disk = Storage::fake('textures');

        $this->get(route('avatar.user', ['uid' => 0]))
            ->assertSuccessful()
            ->assertHeader('Content-Type', 'image/webp');

        $user = User::factory()->create();
        $this->get(route('avatar.user', ['uid' => $user->uid]))
            ->assertSuccessful()
            ->assertHeader('Content-Type', 'image/webp');

        $this->mock(Minecraft::class, function ($mock) {
            $mock->shouldReceive('render2dAvatar')->andReturn(Image::canvas(1, 1));
        });
        $texture = Texture::factory()->create();
        $disk->put($texture->hash, '');
        $user->avatar = $texture->tid;
        $user->save();
        $this->get(route('avatar.user', ['uid' => $user->uid]))
            ->assertSuccessful()
            ->assertHeader('Content-Type', 'image/webp');

        Cache::clear();
        $image = $this->get(route('avatar.user', ['uid' => $user->uid, 'png' => true]))
            ->assertSuccessful()
            ->assertHeader('Content-Type', 'image/png')
            ->getContent();
        $image = Image::make($image);
        $this->assertEquals(100, $image->width());
        $this->assertEquals(100, $image->height());

        $image = $this->get(route('avatar.user', [
            'uid' => $user->uid,
            'size' => 50,
            'png' => true,
        ]))->getContent();
        $image = Image::make($image);
        $this->assertEquals(50, $image->width());
        $this->assertEquals(50, $image->height());
    }

    public function testAvatarByHash()
    {
        $disk = Storage::fake('textures');
        $this->mock(Minecraft::class, function ($mock) {
            $mock->shouldReceive('render2dAvatar')->andReturn(Image::canvas(1, 1));
            $mock->shouldReceive('render3dAvatar')->andReturn(Image::canvas(1, 1));
        });

        $texture = Texture::factory()->create();
        $disk->put($texture->hash, '');
        $this->get(route('avatar.hash', ['hash' => $texture->hash]))
            ->assertSuccessful()
            ->assertHeader('Content-Type', 'image/webp');
    }

    public function testAvatarByTexture()
    {
        $disk = Storage::fake('textures');

        $cape = Texture::factory()->cape()->create();
        $this->get(route('avatar.texture', ['tid' => $cape->tid]))->assertStatus(422);

        $this->get(route('avatar.texture', ['tid' => 0]))
            ->assertSuccessful()
            ->assertHeader('Content-Type', 'image/webp');
        Cache::clear();
        $image = $this->get(route('avatar.texture', ['tid' => 0, 'png' => true]))
            ->assertSuccessful()
            ->assertHeader('Content-Type', 'image/png')
            ->getContent();
        $image = Image::make($image);
        $this->assertEquals(100, $image->width());
        $this->assertEquals(100, $image->height());

        $texture = Texture::factory()->create();
        $this->get(route('avatar.texture', ['tid' => $texture->tid]))
            ->assertSuccessful()
            ->assertHeader('Content-Type', 'image/webp');

        $this->mock(Minecraft::class, function ($mock) {
            $mock->shouldReceive('render2dAvatar')->andReturn(Image::canvas(1, 1));
            $mock->shouldReceive('render3dAvatar')->andReturn(Image::canvas(1, 1));
        });
        $disk->put($texture->hash, '');
        $this->get(route('avatar.texture', ['tid' => $texture->tid]))
            ->assertSuccessful()
            ->assertHeader('Content-Type', 'image/webp');

        Cache::clear();
        $this->get(route('avatar.texture', ['tid' => $texture->tid, 'png' => true]))
            ->assertSuccessful()
            ->assertHeader('Content-Type', 'image/png');
        $image = Image::make($image);
        $this->assertEquals(100, $image->width());
        $this->assertEquals(100, $image->height());
        $this->assertTrue(Cache::has('avatar-2d-t'.$texture->tid.'-s100-png'));

        $image = $this->get(route('avatar.texture', [
            'tid' => $texture->tid,
            'size' => 50,
            'png' => true,
        ]))->getContent();
        $image = Image::make($image);
        $this->assertEquals(50, $image->width());
        $this->assertEquals(50, $image->height());
        $this->assertTrue(Cache::has('avatar-2d-t'.$texture->tid.'-s50-png'));

        $this->get(route('avatar.texture', ['tid' => $texture->tid, '3d' => true]))
            ->assertSuccessful()
            ->assertHeader('Content-Type', 'image/webp');
        $this->assertTrue(Cache::has('avatar-3d-t'.$texture->tid.'-s100-webp'));
    }
}
