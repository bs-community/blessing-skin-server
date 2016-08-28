<?php

namespace App\Http\Middleware;

use App\Models\PlayerModel;

class CheckPlayerExistMiddleware
{
    public function handle($request, \Closure $next)
    {
        if (stripos($request->getUri(), '.json') != false) {
            preg_match('/\/([^\/]*)\.json/', $request->getUri(), $matches);
        } else {
            preg_match('/\/([^\/]*)\.png/', $request->getUri(), $matches);
        }

        $player_name = $matches[1];

        if (\Option::get('allow_chinese_playername')) {
            $player_name = urldecode($player_name);
            // quick fix of chinese playername route parameter problem
            $GLOBALS['player_name'] = $player_name;
        }

        if (!PlayerModel::where('player_name', $player_name)->count()) {
            \Http::abort(404, '角色不存在');
        }

        return $next($request);

    }
}
