<?php

namespace Tests;

use App\Services\Minecraft;
use Illuminate\Http\Testing\FileFactory;
use App\Http\Controllers\TextureController;

class MinecraftTest extends TestCase
{
    private $fileFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fileFactory = new FileFactory();
    }

    public function testGenerateAvatarFromSkin()
    {
        $file = $this->fileFactory->image('skin.png');

        imagepng(imagecreatetruecolor(64, 32), $file->path());
        $avatar = Minecraft::generateAvatarFromSkin(file_get_contents($file->path()), 50);
        $this->assertEquals(50, imagesx($avatar));
        $this->assertEquals(50, imagesy($avatar));

        imagepng(imagecreatetruecolor(128, 64), $file->path());
        $avatar = Minecraft::generateAvatarFromSkin(file_get_contents($file->path()), 50);
        $this->assertEquals(50, imagesx($avatar));
        $this->assertEquals(50, imagesy($avatar));

        $avatar = Minecraft::generateAvatarFromSkin(
            base64_decode(TextureController::getDefaultSteveSkin()), 50, 'l'
        );
        $this->assertEquals(50, imagesx($avatar));
        $this->assertEquals(50, imagesy($avatar));
    }

    public function testGeneratePreviewFromSkin()
    {
        $file = $this->fileFactory->image('skin.png');

        imagepng(imagecreatetruecolor(64, 32), $file->path());
        $preview = Minecraft::generatePreviewFromSkin(
            file_get_contents($file->path()), 50, false, 'front'
        );
        $this->assertEquals(25, imagesx($preview));
        $this->assertEquals(50, imagesy($preview));

        imagepng(imagecreatetruecolor(64, 32), $file->path());
        $preview = Minecraft::generatePreviewFromSkin(
            file_get_contents($file->path()),
            50,
            true, // Alex model
            'both',
            4
        );
        $this->assertEquals(56, imagesx($preview));
        $this->assertEquals(50, imagesy($preview));

        imagepng(imagecreatetruecolor(64, 64), $file->path());
        $preview = Minecraft::generatePreviewFromSkin(
            file_get_contents($file->path()),
            100,
            true, // Alex model
            'both',
            8
        );
        $this->assertEquals(125, imagesx($preview));
        $this->assertEquals(100, imagesy($preview));

        imagepng(imagecreatetruecolor(128, 64), $file->path());
        $preview = Minecraft::generatePreviewFromSkin(file_get_contents($file->path()), 50);
        $this->assertEquals(56, imagesx($preview));
        $this->assertEquals(50, imagesy($preview));

        imagepng(imagecreatetruecolor(128, 128), $file->path());
        $preview = Minecraft::generatePreviewFromSkin(file_get_contents($file->path()), 50);
        $this->assertEquals(56, imagesx($preview));
        $this->assertEquals(50, imagesy($preview));

        $preview = Minecraft::generatePreviewFromSkin(
            base64_decode(TextureController::getDefaultSteveSkin()),
            50,
            false,
            'back'
        );
        $this->assertEquals(25, imagesx($preview));
        $this->assertEquals(50, imagesy($preview));
    }

    public function testGeneratePreviewFromCape()
    {
        $file = $this->fileFactory->image('cape.png');

        imagepng(imagecreatetruecolor(128, 64), $file->path());
        $preview = Minecraft::generatePreviewFromCape(file_get_contents($file->path()), 64);
        $this->assertEquals(40, imagesx($preview));
        $this->assertEquals(64, imagesy($preview));

        imagepng(imagecreatetruecolor(128, 64), $file->path());
        $preview = Minecraft::generatePreviewFromCape(
            file_get_contents($file->path()),
            64,
            281,
            250
        );
        $this->assertEquals(281, imagesx($preview));
        $this->assertEquals(250, imagesy($preview));
    }
}
