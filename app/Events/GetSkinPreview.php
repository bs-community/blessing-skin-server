<?php

namespace App\Events;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class GetSkinPreview extends Event
{
    use SerializesModels;

    public $texture = null;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(\App\Models\Texture $texture)
    {
        $this->texture = $texture;
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
