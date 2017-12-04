<?php

use App\Services\Minecraft;
use org\bovigo\vfs\vfsStream;
use App\Http\Controllers\TextureController;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class MinecraftTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        vfsStream::setup();
    }

    public function testGenerateAvatarFromSkin()
    {
        imagepng(imagecreatetruecolor(64, 32), vfsStream::url('root/skin.png'));
        $avatar = Minecraft::generateAvatarFromSkin(vfsStream::url('root/skin.png'), 50);
        $this->assertEquals(50, imagesx($avatar));
        $this->assertEquals(50, imagesy($avatar));

        imagepng(imagecreatetruecolor(128, 64), vfsStream::url('root/skin.png'));
        $avatar = Minecraft::generateAvatarFromSkin(vfsStream::url('root/skin.png'), 50);
        $this->assertEquals(50, imagesx($avatar));
        $this->assertEquals(50, imagesy($avatar));

        $avatar = Minecraft::generateAvatarFromSkin(
            TextureController::getDefaultSkin(),
            50,
            'f',
            true
        );
        $this->assertEquals(50, imagesx($avatar));
        $this->assertEquals(50, imagesy($avatar));
    }

    public function testGeneratePreviewFromSkin()
    {
        imagepng(imagecreatetruecolor(64, 32), vfsStream::url('root/skin.png'));
        $preview = Minecraft::generatePreviewFromSkin(vfsStream::url('root/skin.png'), 50, true);
        $this->assertEquals(25, imagesx($preview));
        $this->assertEquals(50, imagesy($preview));

        imagepng(imagecreatetruecolor(128, 64), vfsStream::url('root/skin.png'));
        $preview = Minecraft::generatePreviewFromSkin(vfsStream::url('root/skin.png'), 50);
        $this->assertEquals(56, imagesx($preview));
        $this->assertEquals(50, imagesy($preview));

        imagepng(imagecreatetruecolor(128, 128), vfsStream::url('root/skin.png'));
        $preview = Minecraft::generatePreviewFromSkin(vfsStream::url('root/skin.png'), 50);
        $this->assertEquals(56, imagesx($preview));
        $this->assertEquals(50, imagesy($preview));

        $preview = Minecraft::generatePreviewFromSkin(
            TextureController::getDefaultSkin(),
            50,
            true,
            true
        );
        $this->assertEquals(25, imagesx($preview));
        $this->assertEquals(50, imagesy($preview));
    }

    public function testGeneratePreviewFromCape()
    {
        imagepng(imagecreatetruecolor(128, 64), vfsStream::url('root/cape.png'));
        $preview = Minecraft::generatePreviewFromCape(vfsStream::url('root/cape.png'));
        $this->assertEquals(250, imagesx($preview));
        $this->assertEquals(166, imagesy($preview));
    }
}
