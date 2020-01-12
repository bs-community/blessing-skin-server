<?php

namespace App\Http\Middleware;

use App\Models\Player;
use Illuminate\Support\Arr;

class CheckPlayerExist
{
    public function handle($request, \Closure $next)
    {
        $pid = Arr::get($request->route()->parameters, 'pid') ?? $request->input('pid');
        if (!$request->isMethod('get') && !is_null($pid) && is_null(Player::find($pid))) {
            return json(trans('general.unexistent-player'), 1);
        }

        return $next($request);
    }
}
