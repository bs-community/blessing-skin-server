<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;

class RejectBannedUser
{
    public function handle($request, Closure $next)
    {
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
