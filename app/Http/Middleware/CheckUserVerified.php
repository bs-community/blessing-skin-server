<?php

namespace App\Http\Middleware;

class CheckUserVerified
{
    public function handle($request, \Closure $next)
    {
        abort_if(option('require_verification') && ! auth()->user()->verified, 403, trans('auth.check.verified'));

        return $next($request);
    }
}
