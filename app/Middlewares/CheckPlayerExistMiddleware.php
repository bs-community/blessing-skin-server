<?php

namespace App\Middlewares;

use Pecee\Http\Middleware\IMiddleware;
use Pecee\Http\Request;
use App\Models\User;
use App\Models\PlayerModel;
use App\Exceptions\E;

class CheckPlayerExistMiddleware implements IMiddleware
{
    public function handle(Request $request)
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

    }
}
