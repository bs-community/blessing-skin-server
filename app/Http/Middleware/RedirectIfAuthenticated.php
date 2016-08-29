<?php

namespace App\Http\Middleware;

use App\Models\User;
use Session;

class RedirectIfAuthenticated
{
    public function handle($request, \Closure $next)
    {
        if (session()->has('uid')) {
            if (session('token') != (new User(session('uid')))->getToken()) {
                Session::put('msg', '无效的 token，请重新登录~');
            } else {
                return redirect('user');
            }
        }

        return $next($request);
    }
}
