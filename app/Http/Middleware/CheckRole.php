<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    protected $roles = [
        'banned' => User::BANNED,
        'normal' => User::NORMAL,
        'admin' => User::ADMIN,
        'super-admin' => User::SUPER_ADMIN,
    ];

    public function handle(Request $request, Closure $next, $role)
    {
        $permission = $request->user()->permission;
        abort_if($permission < $this->roles[$role], 403);

        return $next($request);
    }
}
