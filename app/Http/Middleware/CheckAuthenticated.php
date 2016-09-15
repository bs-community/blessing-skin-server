<?php

namespace App\Http\Middleware;

use View;
use Http;
use Session;
use App\Models\User;
use App\Models\UserModel;
use App\Exceptions\PrettyPageException;

class CheckAuthenticated
{
    public function handle($request, \Closure $next, $return_user = false)
    {
        if (Session::has('uid')) {
            $user = new User(session('uid'));

            if (session('token') != $user->getToken())
                return redirect('auth/login')->with('msg', trans('auth.check.token'));

            if ($user->getPermission() == "-1") {
                // delete cookies
                setcookie('uid',   '', time() - 3600, '/');
                setcookie('token', '', time() - 3600, '/');

                Session::flush();

                throw new PrettyPageException(trans('auth.check.banned'), 5);
            }

            // ask for filling email
            if ($user->email == "") {
                if (isset($request->email)) {
                    if (filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
                        if (UserModel::where('email', $request->email)->get()->isEmpty()) {
                            $user->setEmail($request->email);
                            // refresh token
                            Session::put('token', $user->getToken(true));
                            setcookie('token', session('token'), time() + 3600, '/');

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

            return $next($request);
        } else {
            return redirect('auth/login')->with('msg', trans('auth.check.anonymous'));
        }

        return $next($request);
    }
}
