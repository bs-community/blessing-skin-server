<?php

namespace App\Http\Middleware;

use App\Exceptions\E;
use Validate;
use Utils;
use View;
use Session;

class CheckPostMiddleware
{
    public function handle($request, \Closure $next)
    {
        if (Utils::getValue('email', $_POST) != "") {
            if (!Validate::email($_POST['email'])) {
                View::json('邮箱或角色名格式错误', 3);
            }
            Session::put('auth_type', 'email');
        } elseif (Utils::getValue('username', $_POST) != "") {
            if (!Validate::playerName($_POST['username'])) {
                View::json('邮箱或角色名格式错误', 3);
            }
            Session::put('auth_type', 'username');
        } else {
            View::json('无效的参数', 3);
        }

        if ($request->getUri() == "/auth/forgot") return $next($request);

        if (isset($_POST['password']) && $_POST['password'] != "") {
            return $next($request);
        } else {
            View::json('密码不能为空哦', 2);
        }
    }
}
