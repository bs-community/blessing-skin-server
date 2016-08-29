<?php

namespace App\Listeners;

use Storage;
use App\Events\PlayerProfileUpdated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class FreshPlayerJson
{
    /**
     * Handle the event.
     *
     * @param  PlayerProfileUpdated  $event
     * @return void
     */
    public function handle(PlayerProfileUpdated $event)
    {
        $player = $event->player;

        $files = [
            "json/{$player->pid}-0",
            "json/{$player->pid}-1"
        ];

        foreach ($files as $file) {
            if (Storage::disk('cache')->has($file)) {
                Storage::disk('cache')->delete($file);
            }
        }
    }
}
