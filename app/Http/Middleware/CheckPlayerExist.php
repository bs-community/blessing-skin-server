<?php

namespace App\Http\Middleware;

use Event;
use App\Models\Player;
use App\Events\CheckPlayerExists;

class CheckPlayerExist
{
    public function handle($request, \Closure $next)
    {
        if ($request->has('pid') && $request->isMethod('post')) {
            if (is_null(Player::find($request->input('pid')))) {
                return response()->json([
                    'errno' => 1,
                    'msg' => trans('general.unexistent-player'),
                ]);
            } else {
                return $next($request);
            }
        }

        if (stripos($request->getUri(), '.json') != false) {
            preg_match('/\/([^\/]*)\.json/', $request->getUri(), $matches);
        } else {
            preg_match('/\/([^\/]*)\.png/', $request->getUri(), $matches);
        }

        $player_name = urldecode($matches[1]);

        $responses = event(new CheckPlayerExists($player_name));

        foreach ($responses as $r) {
            if ($r) {
                return $next($request);
            }    // @codeCoverageIgnore
        }

        if (! Player::where('player_name', $player_name)->get()->isEmpty()) {
            return $next($request);
        }

        if (option('return_204_when_notfound')) {
            return response('', 204, [
                'Cache-Control' => 'public, max-age='.option('cache_expire_time'),
            ]);
        } else {
            return abort(404, trans('general.unexistent-player'));
        }
    }
}
