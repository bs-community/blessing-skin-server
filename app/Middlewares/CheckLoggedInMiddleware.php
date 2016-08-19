<?php

namespace App\Middlewares;

use Pecee\Http\Middleware\IMiddleware;
use Pecee\Http\Request;
use App\Models\User;
use App\Models\UserModel;
use App\Exceptions\E;
use View;
use Http;

class CheckLoggedInMiddleware implements IMiddleware
{
    public function handle(Request $request)
    {
        if (isset($_COOKIE['uid']) && isset($_COOKIE['token'])) {
            $_SESSION['uid']   = $_COOKIE['uid'];
            $_SESSION['token'] = $_COOKIE['token'];
        }

        if (isset($_SESSION['uid'])) {
            $user = new User($_SESSION['uid']);

            if ($_SESSION['token'] != $user->getToken())
                Http::redirect('../auth/login', '无效的 token，请重新登录~');

            if ($user->getPermission() == "-1") {
                // delete cookies
                setcookie('uid',   '', time() - 3600, '/');
                setcookie('token', '', time() - 3600, '/');
                session_destroy();

                throw new E('你已经被本站封禁啦，请联系管理员解决', 5, true);
            }

            // ask for filling email
            if ($user->email == "") {
                if (isset($_POST['email'])) {
                    if (\Validate::email($_POST['email'])) {
                        if (UserModel::where('email', $_POST['email'])->get()->isEmpty()) {
                            $user->setEmail($_POST['email']);
                            // refresh token
                            $_SESSION['token'] = $user->getToken(true);
                            setcookie('token', $_SESSION['token'], time() + 3600, '/');
                            return $user;
                        } else {
                            echo View::make('auth.bind')->with('msg', '该邮箱已被占用');
                        }
                    } else {
                        echo View::make('auth.bind')->with('msg', '邮箱格式错误');
                    }
                    exit;
                }
                View::show('auth.bind');
                exit;
            }

            return $user;
        } else {
            Http::redirect('../auth/login', '非法访问，请先登录');
        }
    }
}
