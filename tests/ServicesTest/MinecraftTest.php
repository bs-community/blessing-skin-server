<?php

namespace Tests;

use App\Services\Minecraft;
use org\bovigo\vfs\vfsStream;
use App\Http\Controllers\TextureController;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class MinecraftTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        vfsStream::setup();
    }

    public function testGenerateAvatarFromSkin()
    {
        imagepng(imagecreatetruecolor(64, 32), vfsStream::url('root/skin.png'));
        $avatar = Minecraft::generateAvatarFromSkin(file_get_contents(vfsStream::url('root/skin.png')), 50);
        $this->assertEquals(50, imagesx($avatar));
        $this->assertEquals(50, imagesy($avatar));

        imagepng(imagecreatetruecolor(128, 64), vfsStream::url('root/skin.png'));
        $avatar = Minecraft::generateAvatarFromSkin(file_get_contents(vfsStream::url('root/skin.png')), 50);
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
        imagepng(imagecreatetruecolor(64, 32), vfsStream::url('root/skin.png'));
        $preview = Minecraft::generatePreviewFromSkin(
            file_get_contents(vfsStream::url('root/skin.png')), 50, false, 'front'
        );
        $this->assertEquals(25, imagesx($preview));
        $this->assertEquals(50, imagesy($preview));

        imagepng(imagecreatetruecolor(64, 32), vfsStream::url('root/skin.png'));
        $preview = Minecraft::generatePreviewFromSkin(
            file_get_contents(vfsStream::url('root/skin.png')),
            50,
            true, // Alex model
            'both',
            4
        );
        $this->assertEquals(56, imagesx($preview));
        $this->assertEquals(50, imagesy($preview));

        imagepng(imagecreatetruecolor(64, 64), vfsStream::url('root/skin.png'));
        $preview = Minecraft::generatePreviewFromSkin(
            file_get_contents(vfsStream::url('root/skin.png')),
            100,
            true, // Alex model
            'both',
            8
        );
        $this->assertEquals(125, imagesx($preview));
        $this->assertEquals(100, imagesy($preview));

        imagepng(imagecreatetruecolor(128, 64), vfsStream::url('root/skin.png'));
        $preview = Minecraft::generatePreviewFromSkin(file_get_contents(vfsStream::url('root/skin.png')), 50);
        $this->assertEquals(56, imagesx($preview));
        $this->assertEquals(50, imagesy($preview));

        imagepng(imagecreatetruecolor(128, 128), vfsStream::url('root/skin.png'));
        $preview = Minecraft::generatePreviewFromSkin(file_get_contents(vfsStream::url('root/skin.png')), 50);
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
        imagepng(imagecreatetruecolor(128, 64), vfsStream::url('root/cape.png'));
        $preview = Minecraft::generatePreviewFromCape(file_get_contents(vfsStream::url('root/cape.png')), 64);
        $this->assertEquals(40, imagesx($preview));
        $this->assertEquals(64, imagesy($preview));

        imagepng(imagecreatetruecolor(128, 64), vfsStream::url('root/cape.png'));
        $preview = Minecraft::generatePreviewFromCape(
            file_get_contents(vfsStream::url('root/cape.png')),
            64,
            281,
            250
        );
        $this->assertEquals(281, imagesx($preview));
        $this->assertEquals(250, imagesy($preview));
    }
}
