<?php

namespace App\Http\Middleware;

use Closure;

class RequireBindPlayer
{
    public function handle($request, Closure $next)
    {
        if (!option('single_player', false)) {
            if ($request->is('user/player/bind')) {
                return redirect('/user');
            } else {
                return $next($request);
            }
        }

        // This allows us to fetch players list.
        if ($request->is('user/player/list')) {
            return $next($request);
        }

        $count = auth()->user()->players()->count();

        if ($request->is('user/player/bind')) {
            if ($count == 1) {
                return redirect('/user');
            } else {
                return $next($request);
            }
        }

        if ($count == 1) {
            return $next($request);
        } else {
            return redirect('user/player/bind');
        }
    }
}
