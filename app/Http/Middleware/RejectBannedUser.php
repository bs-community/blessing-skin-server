<?php

namespace App\Http\Middleware;

use App\Models\User;
use Illuminate\Http\Request;

class RejectBannedUser
{
    public function handle(Request $request, \Closure $next)
    {
        if ($request->route()->getName() === 'auth.logout') {
            return $next($request);
        }

        if ($request->user()->permission == User::BANNED) {
            if ($request->expectsJson()) {
                $response = json(trans('auth.check.banned'), -1);
                $response->setStatusCode(403);

                return $response;
            } else {
                abort(403, trans('auth.check.banned'));
            }
        }

        return $next($request);
    }
}
