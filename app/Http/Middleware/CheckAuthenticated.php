<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Models\UserModel;
use App\Exceptions\PrettyPageException;
use View;
use Http;
use Session;

class CheckAuthenticated
{
    public function handle($request, \Closure $next, $return_user = false)
    {
        if (Session::has('uid')) {
            $user = new User(session('uid'));

            if (session('token') != $user->getToken())
                return redirect('auth/login')->with('msg', '无效的 token，请重新登录');

            if ($user->getPermission() == "-1") {
                // delete cookies
                setcookie('uid',   '', time() - 3600, '/');
                setcookie('token', '', time() - 3600, '/');

                Session::flush();

                throw new PrettyPageException('你已经被本站封禁啦，请联系管理员解决', 5);
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

                            return $user;
                        } else {
                            return View::make('auth.bind')->with('msg', '该邮箱已被占用');
                        }
                    } else {
                        return View::make('auth.bind')->with('msg', '邮箱格式错误');
                    }
                }
                return view('auth.bind');
            }

            if ($return_user)
                return $user;

            return $next($request);
        } else {
            return redirect('auth/login')->with('msg', '非法访问，请先登录');
        }

        return $next($request);
    }
}
