<?php

namespace App\Middlewares;

use Pecee\Http\Middleware\IMiddleware;
use Pecee\Http\Request;
use App\Models\User;

class RedirectIfLoggedInMiddleware implements IMiddleware
{
    public function handle(Request $request)
    {
        if (isset($_COOKIE['email']) && isset($_COOKIE['token'])) {
            $_SESSION['email'] = $_COOKIE['email'];
            $_SESSION['token'] = $_COOKIE['token'];
        }

        if (isset($_SESSION['email'])) {
            if ($_SESSION['token'] != (new User(0, ['email' => $_SESSION['email']]))->getToken())
            {
                $_SESSION['msg'] = "无效的 token，请重新登录~";
            } else {
                \Http::redirect('../user');
            }
        }
    }
}
