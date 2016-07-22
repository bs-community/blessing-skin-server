<?php

namespace App\Middlewares;

use Pecee\Http\Middleware\IMiddleware;
use Pecee\Http\Request;
use App\Models\User;

class CheckLoggedInMiddleware implements IMiddleware
{
    public function handle(Request $request)
    {
        if (isset($_COOKIE['email']) && isset($_COOKIE['token'])) {
            $_SESSION['email'] = $_COOKIE['email'];
            $_SESSION['token'] = $_COOKIE['token'];
        }

        if (isset($_SESSION['email'])) {
            $user = new User($_SESSION['email']);
            if ($_SESSION['token'] != $user->getToken())
                \Http::redirect('../auth/login', '无效的 token，请重新登录~');

            return $user;
        } else {
            \Http::redirect('../auth/login', '非法访问，请先登录');
        }
    }
}
