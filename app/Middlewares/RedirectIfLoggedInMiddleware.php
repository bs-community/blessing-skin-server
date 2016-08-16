<?php

namespace App\Middlewares;

use Pecee\Http\Middleware\IMiddleware;
use Pecee\Http\Request;
use App\Models\User;

class RedirectIfLoggedInMiddleware implements IMiddleware
{
    public function handle(Request $request)
    {
        if (isset($_COOKIE['uid']) && isset($_COOKIE['token'])) {
            $_SESSION['uid']   = $_COOKIE['uid'];
            $_SESSION['token'] = $_COOKIE['token'];
        }

        if (isset($_SESSION['uid'])) {
            if ($_SESSION['token'] != (new User($_SESSION['uid']))->getToken())
            {
                $_SESSION['msg'] = "无效的 token，请重新登录~";
            } else {
                \Http::redirect('../user');
            }
        }
    }
}
