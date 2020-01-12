<?php

namespace Tests\Fakes;

class Minecraft extends \Blessing\Minecraft
{
    public function renderSkin($skin, $ratio = 7.0, $isAlex = false)
    {
        return imagecreate(2, 5);
    }

    public function renderCape($cape, int $height)
    {
        return imagecreate(1, 2);
    }

    public function render2dAvatar($skin, $ratio = 15.0)
    {
        return imagecreate(1, 1);
    }

    public function render3dAvatar($skin, $ratio = 15.0)
    {
        return imagecreate(1, 1);
    }
}
