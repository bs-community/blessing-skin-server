<?php

namespace App\Http\Middleware;

use App;
use Session;
use App\Models\User;

class RedirectIfAuthenticated
{
    public function handle($request, \Closure $next)
    {
        if (session()->has('uid')) {
            if (session('token') != App::make('users')->get(session('uid'))->getToken()) {
                Session::put('msg', trans('auth.check.token'));
            } else {
                return redirect('user');
            }
        }

        return $next($request);
    }
}
