<?php

namespace App\Events;

use App\Events\Event;
use App\Models\Texture;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

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

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }
}
