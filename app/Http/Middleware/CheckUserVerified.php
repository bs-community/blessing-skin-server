<?php

namespace App\Http\Middleware;

class CheckUserVerified
{
    public function handle($request, \Closure $next)
    {
        if (option('require_verification') && ! auth()->user()->verified) {
            abort(403, trans('auth.check.verified'));
        }

        return $next($request);
    }
}
