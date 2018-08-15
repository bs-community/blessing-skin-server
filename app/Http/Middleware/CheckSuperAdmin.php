<?php

namespace App\Http\Middleware;

use Symfony\Component\HttpFoundation\Response;

class CheckSuperAdmin
{
    public function handle($request, \Closure $next)
    {
        $result = (new CheckAuthenticated)->handle($request, $next, true);

        if ($result instanceof Response) {
            return $result;
        }

        if (! $result->isSuperAdmin()) {
            abort(403, trans('auth.check.super-admin'));
        }

        return $next($request);
    }
}
