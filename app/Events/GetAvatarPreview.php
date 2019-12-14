<?php

namespace App\Events;

use App\Models\Texture;

class GetAvatarPreview extends Event
{
    public $size;

    public $texture;

    /**
     * Create a new event instance.
     *
     * @param int $size
     *
     * @return void
     */
    public function __construct(Texture $texture, $size)
    {
        $this->texture = $texture;
        $this->size = $size;
    }
}
