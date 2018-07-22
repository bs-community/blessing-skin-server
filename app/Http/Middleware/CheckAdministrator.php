<?php

namespace App\Http\Middleware;

use Symfony\Component\HttpFoundation\Response;

class CheckAdministrator
{
    public function handle($request, \Closure $next)
    {
        $result = (new CheckAuthenticated)->handle($request, $next, true);

        if ($result instanceof Response) {
            return $result;
        }

        if (! $result->isAdmin()) {
            abort(403, trans('auth.check.admin'));
        }

        return $next($request);
    }
}
