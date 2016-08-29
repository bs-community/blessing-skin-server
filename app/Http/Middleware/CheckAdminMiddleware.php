<?php

namespace App\Http\Middleware;

class CheckAdminMiddleware
{
    public function handle($request, \Closure $next)
    {
        $user = (new CheckAuthenticated)->handle($request, $next, true);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        if (!$user->is_admin) {
            return redirect('user')->with('msg', '看起来你并不是管理员哦');
        }

        return $next($request);
    }
}
