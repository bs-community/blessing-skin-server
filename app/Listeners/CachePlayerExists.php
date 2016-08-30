<?php

namespace App\Listeners;

use App\Models\PlayerModel;
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

        if (!\Storage::disk('cache')->has("notfound/$player_name")) {
            if (PlayerModel::where('player_name', $player_name)->get()->isEmpty()) {
                \Storage::disk('cache')->put("notfound/$player_name", '');
            }
        } else {
            abort(404, '角色不存在');
        }
    }
}
