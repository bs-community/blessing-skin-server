<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    protected $roles = [
        'banned' => -1,
        'normal' => 0,
        'admin' => 1,
        'super-admin' => 2,
    ];

    public function handle(Request $request, Closure $next, $role)
    {
        $permission = $request->user()->permission;
        abort_if($permission < $this->roles[$role], 403);

        return $next($request);
    }
}
