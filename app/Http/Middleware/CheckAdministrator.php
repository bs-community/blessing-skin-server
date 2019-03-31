<?php

namespace App\Http\Middleware;

class CheckAdministrator
{
    public function handle($request, \Closure $next)
    {
        abort_unless(auth()->user()->isAdmin(), 403, trans('auth.check.admin'));
        return $next($request);
    }
}
