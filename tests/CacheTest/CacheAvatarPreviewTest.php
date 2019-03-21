<?php

namespace Tests;

use Cache;
use Event;
use Mockery;
use Storage;
use App\Models\Texture;
use App\Events\GetAvatarPreview;
use Illuminate\Http\UploadedFile;
use App\Listeners\CacheAvatarPreview;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CacheAvatarPreviewTest extends TestCase
{
    use DatabaseTransactions;

    public function testHandle()
    {
        Event::listen(GetAvatarPreview::class, CacheAvatarPreview::class);

        $texture = factory(Texture::class)->create();
        Storage::disk('textures')
            ->putFileAs('.', UploadedFile::fake()->image($texture->hash), $texture->hash);
        $mock = Mockery::mock('overload:Minecraft');
        $mock->shouldReceive('generateAvatarFromSkin')->andReturn(imagecreatetruecolor(1, 1));

        event(new GetAvatarPreview($texture, 45));
        $this->assertTrue(Cache::has("avatar-{$texture->tid}-45"));
    }
}
