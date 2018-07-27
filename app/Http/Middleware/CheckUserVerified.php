<?php

namespace App\Http\Middleware;

class CheckUserVerified
{
    public function handle($request, \Closure $next)
    {
        $result = (new CheckAuthenticated)->handle($request, $next, true);

        if ($result instanceof Response) {
            return $result;
        }

        if (option('require_verification') && !$result->verified) {
            abort(403, trans('auth.check.verified'));
        }

        return $next($request);
    }
}
