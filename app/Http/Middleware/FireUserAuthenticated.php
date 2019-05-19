<?php

namespace App\Http\Middleware;

use Closure;

class FireUserAuthenticated
{
    public function handle($request, Closure $next)
    {
        if (auth()->check()) {
            event(new \App\Events\UserAuthenticated($request->user()));
        }

        return $next($request);
    }
}
