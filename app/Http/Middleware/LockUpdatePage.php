<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;

class LockUpdatePage
{
    public function handle($request, Closure $next)
    {
        abort_if($request->user()->permission < User::SUPER_ADMIN, 503);

        return $next($request);
    }
}
