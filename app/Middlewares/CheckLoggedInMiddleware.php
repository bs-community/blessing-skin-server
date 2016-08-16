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
        if (isset($_COOKIE['uid']) && isset($_COOKIE['token'])) {
            $_SESSION['uid']   = $_COOKIE['uid'];
            $_SESSION['token'] = $_COOKIE['token'];
        }

        if (isset($_SESSION['uid'])) {
            $user = new User($_SESSION['uid']);

            if ($_SESSION['token'] != $user->getToken())
                \Http::redirect('../auth/login', '无效的 token，请重新登录~');

            if ($user->getPermission() == "-1") {
                // delete cookies
                setcookie("uid", "", time() - 3600, '/');
                setcookie("token", "", time() - 3600, '/');
                session_destroy();

                throw new E('你已经被本站封禁啦，请联系管理员解决', 5, true);
            }

            return $user;
        } else {
            \Http::redirect('../auth/login', '非法访问，请先登录');
        }
    }
}
