<?php

namespace Tests;

use App\Models\Player;
use App\Models\Texture;
use App\Models\User;
use Cache;
use Carbon\Carbon;
use Event;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Image;
use Mockery;

class TextureControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function testJson()
    {
        $steve = factory(Texture::class)->create();

        // Player is not existed
        $this->get('/nope.json')
            ->assertSee(trans('general.unexistent-player'))
            ->assertStatus(404);

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

        option(['enable_json_cache' => true]);
        Cache::shouldReceive('rememberForever')
            ->withArgs(function ($key, $closure) use ($player) {
                $this->assertEquals('json-'.$player->pid, $key);
                $this->assertEquals($player->toJson(), $closure());

                return true;
            })
            ->once()
            ->andReturn($player->toJson());
        $this->getJson("/{$player->name}.json")
            ->assertJson([
                'username' => $player->name,
                'skins' => [
                    'default' => $steve->hash,
                ],
                'cape' => null,
            ])->assertHeader('Last-Modified');
    }

    public function testTexture()
    {
        Storage::fake('textures');
        $steve = factory(Texture::class)->create();
        Storage::disk('textures')->put($steve->hash, '');
        $this->get('/textures/nope')
            ->assertSee('404');

        $this->get('/textures/'.$steve->hash)
            ->assertHeader('Content-Type', 'image/png')
            ->assertHeader('Last-Modified')
            ->assertHeader('ETag')
            ->assertHeader('Cache-Control', 'max-age='.option('cache_expire_time').', public')
            ->assertHeader('Accept-Ranges', 'bytes')
            ->assertHeader('Content-Length', Storage::disk('textures')->size($steve->hash))
            ->assertSuccessful();

        // Cache test
        $this->get('/textures/'.$steve->hash, [
            'If-None-Match' => md5(''),
        ])->assertStatus(304);
        $this->get('/textures/'.$steve->hash, [
            'If-Modified-Since' => Carbon::now()->addHours(1)->toRfc7231String(),
        ])->assertStatus(304);

        Storage::shouldReceive('disk')->with('textures')->andThrow(new Exception());
        $this->get('/textures/'.$steve->hash)->assertNotFound();
    }

    public function testAvatarByTid()
    {
        $this->get('/avatar/1')->assertHeader('Content-Type', 'image/png');
    }

    public function testAvatar()
    {
        Event::fake();

        Storage::fake('textures');
        $image = $this->get('/avatar/user/5/45')
            ->assertHeader('Content-Type', 'image/png')
            ->getContent();
        $image = Image::make($image);
        $this->assertEquals(45, $image->width());
        $this->assertEquals(45, $image->height());

        $steve = factory(Texture::class)->create();
        $png = base64_decode(\App\Http\Controllers\TextureController::getDefaultSteveSkin());
        Storage::disk('textures')->put($steve->hash, $png);

        $user = factory(User::class)->create(['avatar' => $steve->tid]);

        $mock = Mockery::mock('overload:Minecraft');
        $mock->shouldReceive('generateAvatarFromSkin')
            ->once()
            ->andReturn(imagecreatefromstring($png));

        $this->get('/avatar/user/'.$user->uid)
            ->assertHeader('Content-Type', 'image/png');
        Event::assertDispatched(\App\Events\GetAvatarPreview::class);

        Storage::shouldReceive('disk')->with('textures')->andThrow(new Exception());
        $image = $this->get('/avatar/user/'.$user->uid.'/45')
            ->assertHeader('Content-Type', 'image/png')
            ->getContent();
        $image = Image::make($image);
        $this->assertEquals(45, $image->width());
        $this->assertEquals(45, $image->height());
    }

    public function testPreview()
    {
        Event::fake();
        Storage::fake('textures');

        $steve = factory(Texture::class)->create();
        $cape = factory(Texture::class, 'cape')->create();

        $this->get('/preview/0')
            ->assertHeader('Content-Type', 'image/png');

        $this->get("/preview/{$steve->tid}")
            ->assertHeader('Content-Type', 'image/png');

        $png = base64_decode(\App\Http\Controllers\TextureController::getDefaultSteveSkin());
        Storage::disk('textures')->put($steve->hash, $png);
        Storage::disk('textures')->put($cape->hash, $png);

        $mock = Mockery::mock('overload:Minecraft');
        $mock->shouldReceive('generatePreviewFromSkin')
            ->once()
            ->andReturn(imagecreatefromstring($png));
        $this->get("/preview/{$steve->tid}/56")
            ->assertHeader('Content-Type', 'image/png');
        Event::fake(\App\Events\GetSkinPreview::class);

        $mock->shouldReceive('generatePreviewFromCape')
            ->once()
            ->andReturn(imagecreatefromstring($png));
        $this->get("/preview/{$cape->tid}")
            ->assertHeader('Content-Type', 'image/png');

        Storage::shouldReceive('disk')->with('textures')->andThrow(new Exception());
        $this->get("/preview/{$steve->tid}")
            ->assertHeader('Content-Type', 'image/png');
    }

    public function testRaw()
    {
        Storage::fake('textures');
        $steve = factory(Texture::class)->create();
        Storage::disk('textures')->put($steve->hash, '');

        // Not found
        $this->get('/raw/0.png')
            ->assertNotFound()
            ->assertSee(trans('skinlib.non-existent'));

        // Success
        $this->get("/raw/{$steve->tid}.png")
            ->assertHeader('Content-Type', 'image/png');

        // Texture is deleted
        Storage::disk('textures')->delete($steve->hash);
        $this->get("/raw/{$steve->tid}.png")->assertNotFound();

        // Disallow downloading texture directly
        option(['allow_downloading_texture' => false]);
        $this->get("/raw/{$steve->tid}.png")->assertNotFound();
    }

    public function testAvatarByPlayer()
    {
        Storage::fake('textures');

        // No such player.
        $this->get('/avatar/player/1/abc.png')->assertNotFound();

        // No such texture.
        $player = factory(Player::class)->create();
        $this->get("/avatar/player/1/{$player->name}.png")->assertNotFound();

        $texture = factory(Texture::class)->create();
        $player->tid_skin = $texture->tid;
        $player->save();
        $this->get("/avatar/player/1/{$player->name}.png")->assertNotFound();

        // Success
        $png = base64_decode(\App\Http\Controllers\TextureController::getDefaultSteveSkin());
        Storage::disk('textures')->put($texture->hash, $png);
        Mockery::mock('overload:Minecraft')
            ->shouldReceive('generateAvatarFromSkin')
            ->once()
            ->andReturn(imagecreatefromstring($png));
        $this->get("/avatar/player/20/{$player->name}.png")->assertSuccessful();
        Storage::disk('textures')->delete($texture->hash);
    }
}
