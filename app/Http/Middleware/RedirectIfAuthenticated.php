<?php

namespace App\Http\Middleware;

use App\Models\User;
use Session;

class RedirectIfAuthenticated
{
    public function handle($request, \Closure $next)
    {
        if (isset($_COOKIE['uid']) && isset($_COOKIE['token'])) {
            Session::put('uid'  , $_COOKIE['uid']);
            Session::put('token', $_COOKIE['token']);
        }

        if (session()->has('uid')) {
            if (session('token') != (new User(session('uid')))->getToken())
            {
                Session::put('msg', '无效的 token，请重新登录~');
            } else {
                \Http::redirect('../user');
            }
        }

        return $next($request);
    }
}
