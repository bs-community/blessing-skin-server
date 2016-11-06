<?php

namespace App\Http\Middleware;

use Event;
use App\Models\Player;
use App\Events\CheckPlayerExists;

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

        $responses = Event::fire(new CheckPlayerExists($player_name));

        foreach ($responses as $r) {
            if ($r) return $next($request);
        }

        if (!Player::where('player_name', $player_name)->get()->isEmpty())
            return $next($request);

        if (option('return_200_when_notfound') == "1") {
            return json([
                'player_name' => $player_name,
                'errno'       => 404,
                'msg'         => 'Player Not Found.'
            ])->header('Cache-Control', 'public, max-age='.option('cache_expire_time'));
        } else {
            abort(404, trans('general.unexistent-player'));
        }

    }
}
