<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Player;

class CheckPlayerOwner
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($pid = $request->input('pid')) {
            $player = Player::find($pid);

            if ($player->uid != auth()->id()) {
                return json(trans('admin.players.no-permission'), 1);
            }
        }

        return $next($request);
    }
}
