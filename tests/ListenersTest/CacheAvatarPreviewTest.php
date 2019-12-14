<?php

namespace Tests;

use App\Events\GetAvatarPreview;
use App\Models\Texture;
use Cache;
use Event;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Mockery;
use Storage;

class CacheAvatarPreviewTest extends TestCase
{
    use DatabaseTransactions;

    public function testHandle()
    {
        option(['enable_avatar_cache' => true]);
        $provider = new \App\Providers\EventServiceProvider(app());
        $provider->boot();
        Storage::fake('textures');

        $texture = factory(Texture::class)->create();
        Storage::disk('textures')
            ->putFileAs('.', UploadedFile::fake()->image($texture->hash), $texture->hash);
        $mock = Mockery::mock('overload:Minecraft');
        $mock->shouldReceive('generateAvatarFromSkin')->andReturn(imagecreatetruecolor(1, 1));

        event(new GetAvatarPreview($texture, 45));
        $this->assertTrue(Cache::has("avatar-{$texture->tid}-45"));
    }
}
