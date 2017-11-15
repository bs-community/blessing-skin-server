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

            if ($player->uid != app('user.current')->uid) {
                return response()->json([
                    'errno' => 1,
                    'msg' => trans('admin.players.no-permission')
                ]);
            }
        }

        return $next($request);
    }
}
