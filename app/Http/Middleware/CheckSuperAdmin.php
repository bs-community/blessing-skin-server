<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;

class CheckSuperAdmin
{
    public function handle($request, Closure $next)
    {
        if (auth()->user()->permission != User::SUPER_ADMIN) {
            abort(403, trans('auth.check.super-admin'));
        }

        return $next($request);
    }
}
