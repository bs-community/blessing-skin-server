<?php

namespace App\Events;

class TextureDeleting extends Event
{
    public $texture;

    public function __construct(\App\Models\Texture $texture)
    {
        $this->texture = $texture;
    }
}
