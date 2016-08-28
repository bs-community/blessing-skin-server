<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Models\UserModel;
use App\Exceptions\E;
use View;
use Http;
use Session;

class CheckAuthenticated
{
    public function handle($request, \Closure $next, $return_user = false)
    {
        if (isset($_COOKIE['uid']) && isset($_COOKIE['token'])) {
            Session::put('uid'  , $_COOKIE['uid']);
            Session::put('token', $_COOKIE['token']);
        }

        if (Session::has('uid')) {
            $user = new User(session('uid'));

            if (session('token') != $user->getToken())
                Http::redirect('../auth/login', '无效的 token，请重新登录~');

            if ($user->getPermission() == "-1") {
                // delete cookies
                setcookie('uid',   '', time() - 3600, '/');
                setcookie('token', '', time() - 3600, '/');
                Session::flush();
                Session::save();

                throw new E('你已经被本站封禁啦，请联系管理员解决', 5, true);
            }

            // ask for filling email
            if ($user->email == "") {
                if (isset($_POST['email'])) {
                    if (\Validate::email($_POST['email'])) {
                        if (UserModel::where('email', $_POST['email'])->get()->isEmpty()) {
                            $user->setEmail($_POST['email']);
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
                    exit;
                }
                return view('auth.bind');
                exit;
            }

            if ($return_user)
                return $user;

            return $next($request);
        } else {
            Http::redirect('../auth/login', '非法访问，请先登录');
        }

        return $next($request);
    }
}
