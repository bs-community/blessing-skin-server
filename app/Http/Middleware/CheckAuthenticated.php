<?php

namespace App\Http\Middleware;

use App;
use View;
use Http;
use Cookie;
use Session;
use App\Models\User;
use App\Events\UserAuthenticated;
use App\Exceptions\PrettyPageException;

class CheckAuthenticated
{
    public function handle($request, \Closure $next, $return_user = false)
    {
        if (Session::has('uid')) {
            $user = app('users')->get(session('uid'));

            if (session('token') != $user->getToken())
                return redirect('auth/login')->with('msg', trans('auth.check.token'));

            if ($user->getPermission() == "-1") {
                delete_sessions();
                delete_cookies();

                throw new PrettyPageException(trans('auth.check.banned'), 5);
            }

            // ask for filling email
            if ($user->email == "") {
                if (isset($request->email)) {
                    if (filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
                        if (User::where('email', $request->email)->get()->isEmpty()) {
                            $user->setEmail($request->email);
                            // refresh token
                            Session::put('token',  $user->getToken(true));
                            Cookie::queue('token', $user->getToken(), 60);

                            return $next($request);
                        } else {
                            echo View::make('auth.bind')->with('msg', trans('auth.validation.email'));
                        }
                    } else {
                        echo View::make('auth.bind')->with('msg', trans('auth.bind.registered'));
                    }
                    exit;
                }
                View::show('auth.bind');
                exit;
            }

            if ($return_user)
                return $user;

            event(new UserAuthenticated($user));

            return $next($request);
        } else {
            return redirect('auth/login')->with('msg', trans('auth.check.anonymous'));
        }

        return $next($request);
    }
}
