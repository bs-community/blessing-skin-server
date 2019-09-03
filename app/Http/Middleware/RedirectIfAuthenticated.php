<?php

namespace App\Http\Middleware;

use Auth;

class RedirectIfAuthenticated
{
    public function handle($request, \Closure $next)
    {
        return Auth::check() ? redirect('user') : $next($request);
    }
}
