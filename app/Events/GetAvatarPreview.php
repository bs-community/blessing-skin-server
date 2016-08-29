<?php

namespace App\Events;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

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
