<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Player;
use Illuminate\Support\Arr;

class CheckPlayerOwner
{
    public function handle($request, Closure $next)
    {
        $pid = Arr::get($request->route()->parameters, 'pid') ?? $request->input('pid');
        if ($pid && ($player = Player::find($pid)) && $player->uid != auth()->id()) {
            return json(trans('admin.players.no-permission'), 1);
        }

        return $next($request);
    }
}
