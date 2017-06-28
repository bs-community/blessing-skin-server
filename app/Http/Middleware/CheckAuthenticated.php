<?php

namespace App\Http\Middleware;

use App;
use View;
use Http;
use Cookie;
use Session;
use Closure;
use App\Models\User;
use App\Events\UserAuthenticated;

class CheckAuthenticated
{
    public function handle($request, Closure $next, $returnUser = false)
    {
        if (Session::has('uid')) {

            if (!app()->bound('user.current')) {
                // bind current user to container
                $user = app('users')->get(session('uid'));
                app()->instance('user.current', $user);
            } else {
                $user = app('user.current');
            }

            if (session('token') != $user->getToken()) {
                $this->flashLastRequestedPath();
                return redirect('auth/login')->with('msg', trans('auth.check.token'));
            }

            if ($user->getPermission() == "-1") {
                delete_sessions();
                delete_cookies();

                abort(403, trans('auth.check.banned'));
            }

            // ask for filling email
            if ($user->email == "") {
                return $this->askForFillingEmail($request, $next);
            }

            event(new UserAuthenticated($user));

            return $returnUser ? $user : $next($request);

        } else {
            $this->flashLastRequestedPath();

            return redirect('auth/login')->with('msg', trans('auth.check.anonymous'));
        }

        return $next($request);
    }

    public function askForFillingEmail($request, Closure $next)
    {
        $user = app('user.current');

        if (isset($request->email)) {
            if (filter_var($request->email, FILTER_VALIDATE_EMAIL)) {

                if (User::where('email', $request->email)->get()->isEmpty()) {
                    $user->setEmail($request->email);
                    // refresh token
                    Session::put('token',  $user->getToken(true));
                    Cookie::queue('token', $user->getToken(), 60);

                    return $next($request);
                } else {
                    return response()->view('auth.bind', ['msg' => trans('auth.bind.registered')]);
                }
            } else {
                return response()->view('auth.bind', ['msg' => trans('auth.validation.email')]);
            }
        }

        return response()->view('auth.bind');
    }

    protected function flashLastRequestedPath($path = null)
    {
        $path = $path ?: app('request')->path();
        
        return session(['last_requested_path' => $path]);
    }
}
