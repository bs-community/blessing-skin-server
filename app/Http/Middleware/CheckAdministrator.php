<?php

namespace App\Http\Middleware;

class CheckAdministrator
{
    public function handle($request, \Closure $next)
    {
        $result = (new CheckAuthenticated)->handle($request, $next, true);

        if ($result instanceof \Illuminate\Http\RedirectResponse) {
            return $result;
        }

        if (! $result->isAdmin()) {
            abort(403, trans('auth.check.admin'));
        }

        return $next($request);
    }
}
