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
        // Load session from cookie
        if ($request->cookie('uid') && $request->cookie('token')) {
            Session::put('uid'  , $request->cookie('uid'));
            Session::put('token', $request->cookie('token'));
        }

        if (Session::has('uid')) {
            $user = User::find(session('uid'));

            if ($user && $user->getToken() == session('token')) {
                // Push user instance into repository
                app('users')->set($user->uid, $user);
                // Bind current user to container
                app()->instance('user.current', $user);
            } else {
                // Remove sessions & cookies
                delete_sessions();
                delete_cookies();

                return redirect('auth/login')->with('msg', trans('auth.check.token'));
            }
        }

        return $next($request);
    }
}
