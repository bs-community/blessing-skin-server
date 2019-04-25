<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            session([
                'last_requested_path' => $request->path(),
                'msg' => trans('auth.check.anonymous'),
            ]);
            return '/auth/login';
        }
    }
}
