<?php

namespace App\Listeners;

use Storage;
use App\Events\GetPlayerJson;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CachePlayerJson
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

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

        if (!Storage::disk('cache')->has("json/{$player->pid}-{$api_type}")) {
            Storage::disk('cache')->put("json/{$player->pid}-{$api_type}", $player->generateJsonProfile($api_type));
        }

    }
}
