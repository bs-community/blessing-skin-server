<?php

namespace App\Listeners;

use Storage;
use App\Models\Player;
use App\Events\CheckPlayerExists;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CachePlayerExists
{
    /**
     * Handle the event.
     *
     * @param  CheckPlayerExists  $event
     * @return void
     */
    public function handle(CheckPlayerExists $event)
    {
        $player_name = $event->player_name;

        if ($player_name && !Storage::disk('cache')->has("notfound/$player_name")) {
            if (Player::where('player_name', $player_name)->get()->isEmpty()) {
                Storage::disk('cache')->put("notfound/$player_name", '');
            }
        } else {
            if (option('return_200_when_notfound') == "1") {
                return json([
                    'player_name' => $player_name,
                    'message'     => 'Player Not Found.'
                ]);
            } else {
                abort(404, trans('general.unexistent-player'));
            }
        }
    }
}
