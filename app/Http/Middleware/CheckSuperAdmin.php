<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;

class CheckSuperAdmin
{
    public function handle($request, Closure $next)
    {
        abort_if(auth()->user()->permission != User::SUPER_ADMIN, 403, trans('auth.check.super-admin'));

        return $next($request);
    }
}
