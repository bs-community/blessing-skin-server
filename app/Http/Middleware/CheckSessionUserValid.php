<?php

namespace App\Http\Middleware;

use App;
use Cookie;
use Session;
use App\Models\User;

class CheckSessionUserValid
{
    public function handle($request, \Closure $next)
    {
        // load session from cookie
        if ($request->cookie('uid') && $request->cookie('token')) {
            Session::put('uid'  , $request->cookie('uid'));
            Session::put('token', $request->cookie('token'));
        }

        if (Session::has('uid')) {
            $user = User::find(session('uid'));

            if ($user && $user->getToken() == session('token')) {
                // push user instance to repository
                app('users')->set($user->uid, $user);
            } else {
                // remove sessions & cookies
                delete_sessions();
                delete_cookies();
            }
        }

        return $next($request);
    }
}
