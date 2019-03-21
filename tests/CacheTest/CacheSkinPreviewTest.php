<?php

namespace Tests;

use Cache;
use Event;
use Mockery;
use Storage;
use App\Models\Texture;
use App\Events\GetSkinPreview;
use Illuminate\Http\UploadedFile;
use App\Listeners\CacheSkinPreview;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CacheSkinPreviewTest extends TestCase
{
    use DatabaseTransactions;

    public function testHandle()
    {
        Event::listen(GetSkinPreview::class, CacheSkinPreview::class);

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
