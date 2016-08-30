<?php

namespace App\Http\Middleware;

use App\Models\PlayerModel;
use App\Events\CheckPlayerExists;
use Event;

class CheckPlayerExistMiddleware
{
    public function handle($request, \Closure $next)
    {
        if (stripos($request->getUri(), '.json') != false) {
            preg_match('/\/([^\/]*)\.json/', $request->getUri(), $matches);
        } else {
            preg_match('/\/([^\/]*)\.png/', $request->getUri(), $matches);
        }

        $player_name = urldecode($matches[1]);

        Event::fire(new CheckPlayerExists($player_name));

        if (PlayerModel::where('player_name', $player_name)->get()->isEmpty()) {
            abort(404, '角色不存在');
        }

        return $next($request);

    }
}
