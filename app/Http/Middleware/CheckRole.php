<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    protected $roles = [
        'banned' => USER::BANNED,
        'normal' => USER::NORMAL,
        'admin' => USER::ADMIN,
        'super-admin' => USER::SUPER_ADMIN,
    ];

    public function handle(Request $request, Closure $next, $role)
    {
        $permission = $request->user()->permission;
        abort_if($permission < $this->roles[$role], 403);

        return $next($request);
    }
}
