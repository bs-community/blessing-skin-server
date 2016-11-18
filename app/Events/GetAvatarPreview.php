<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;

class GetAvatarPreview extends Event
{
    use SerializesModels;

    public $texture;

    public $size;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(\App\Models\Texture $texture, $size)
    {
        $this->texture  = $texture;
        $this->size = $size;
    }

}
