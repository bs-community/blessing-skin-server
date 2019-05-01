<?php

namespace App\Http\Middleware;

use Event;
use App\Models\Player;
use Illuminate\Support\Arr;
use App\Events\CheckPlayerExists;

class CheckPlayerExist
{
    public function handle($request, \Closure $next)
    {
        $pid = Arr::get($request->route()->parameters, 'pid') ?? $request->input('pid');
        if (! $request->isMethod('get') && ! is_null($pid)) {
            if (is_null(Player::find($pid))) {
                return json(trans('general.unexistent-player'), 1);
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

        if (is_array($responses)) {
            // @codeCoverageIgnoreStart
            foreach ($responses as $r) {
                if ($r) {
                    return $next($request);
                }
            }
            // @codeCoverageIgnoreEnd
        }

        if (! Player::where('name', $player_name)->get()->isEmpty()) {
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
