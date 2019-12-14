<?php

namespace App\Http\Middleware;

use Closure;

class EnsureEmailFilled
{
    public function handle($request, Closure $next)
    {
        if ($request->user()->email != '' && $request->is('auth/bind')) {
            return redirect('/user');
        } elseif ($request->user()->email == '' && !$request->is('auth/bind')) {
            return redirect('/auth/bind');
        }

        return $next($request);
    }
}
