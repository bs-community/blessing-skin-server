<?php

namespace App\Middlewares;

use Pecee\Http\Middleware\IMiddleware;
use Pecee\Http\Request;
use App\Models\User;
use App\Exceptions\E;

class CheckLoggedInMiddleware implements IMiddleware
{
    public function handle(Request $request)
    {
        if (isset($_COOKIE['email']) && isset($_COOKIE['token'])) {
            $_SESSION['email'] = $_COOKIE['email'];
            $_SESSION['token'] = $_COOKIE['token'];
        }

        if (isset($_SESSION['email'])) {
            $user = new User(0, ['email' => $_SESSION['email']]);

            if ($_SESSION['token'] != $user->getToken())
                \Http::redirect('../auth/login', '无效的 token，请重新登录~');

            if ($user->getPermission() == "-1") {
                // delete cookies
                setcookie("email", "", time() - 3600, '/');
                setcookie("token", "", time() - 3600, '/');
                session_destroy();

                throw new E('你已经被本站封禁啦，请联系管理员解决', -1, true);
            }

            return $user;
        } else {
            \Http::redirect('../auth/login', '非法访问，请先登录');
        }
    }
}
