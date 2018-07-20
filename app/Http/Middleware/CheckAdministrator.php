<?php

namespace App\Http\Middleware;

class CheckAdministrator
{
    public function handle($request, \Closure $next)
    {
        if (! auth()->user()->isAdmin()) {
            abort(403, trans('auth.check.admin'));
        }

        return $next($request);
    }
}
