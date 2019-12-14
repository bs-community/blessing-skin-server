<?php

namespace Tests;

use App\Events\GetSkinPreview;
use App\Models\Texture;
use Cache;
use Event;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Mockery;
use Storage;

class CacheSkinPreviewTest extends TestCase
{
    use DatabaseTransactions;

    public function testHandle()
    {
        option(['enable_preview_cache' => true]);
        $provider = new \App\Providers\EventServiceProvider(app());
        $provider->boot();
        Storage::fake('textures');

        $skin = factory(Texture::class)->create();
        Storage::disk('textures')
            ->putFileAs('.', UploadedFile::fake()->image($skin->hash), $skin->hash);
        $mock = Mockery::mock('overload:Minecraft');
        $mock->shouldReceive('generatePreviewFromSkin')->andReturn(imagecreatetruecolor(1, 1));

        event(new GetSkinPreview($skin, 45));
        $this->assertTrue(Cache::has("preview-{$skin->tid}-45"));

        $cape = factory(Texture::class, 'cape')->create();
        Storage::disk('textures')
            ->putFileAs('.', UploadedFile::fake()->image($cape->hash), $cape->hash);
        $mock->shouldReceive('generatePreviewFromCape')->andReturn(imagecreatetruecolor(1, 1));

        event(new GetSkinPreview($cape, 45));
        $this->assertTrue(Cache::has("preview-{$cape->tid}-45"));
    }
}
