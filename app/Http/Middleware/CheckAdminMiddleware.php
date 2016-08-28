<?php

namespace App\Http\Middleware;

class CheckAdminMiddleware
{
    public function handle($request, \Closure $next)
    {
        $user = (new CheckAuthenticated)->handle($request, $next, true);

        if (!$user->is_admin) {
            \Http::redirect('../user', '看起来你并不是管理员哦');
        }

        return $next($request);
    }
}
