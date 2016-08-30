<?php

namespace App\Listeners;

use Storage;
use App\Events\GetPlayerJson;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CachePlayerJson
{
    /**
     * Handle the event.
     *
     * @param  GetPlayerJson  $event
     * @return void
     */
    public function handle(GetPlayerJson $event)
    {
        $player   = $event->player;
        $api_type = $event->api_type;

        $filename = "json/{$player->pid}-{$api_type}";

        if (!Storage::disk('cache')->has($filename)) {
            Storage::disk('cache')->put($filename, $player->generateJsonProfile($api_type));
        }

        return \Storage::disk('cache')->get($filename);
    }
}
