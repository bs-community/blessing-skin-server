<?php

namespace App\Events;

use App\Models\Texture;
use Illuminate\Queue\SerializesModels;

class GetSkinPreview extends Event
{
    use SerializesModels;

    public $texture;

    public $size;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Texture $texture, $size)
    {
        $this->texture = $texture;
        $this->size = $size;
    }

}
