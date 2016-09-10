<?php

namespace App\Listeners;

use Storage;
use App\Events\PlayerWasAdded;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class FreshNotFoundCache
{
    /**
     * Handle the event.
     *
     * @param  PlayerWasAdded  $event
     * @return void
     */
    public function handle(PlayerWasAdded $event)
    {
        $player_name = $event->player->player_name;

        if ($player_name && Storage::disk('cache')->has("notfound/$player_name")) {
            Storage::disk('cache')->delete("notfound/$player_name", '');
        }
    }
}
