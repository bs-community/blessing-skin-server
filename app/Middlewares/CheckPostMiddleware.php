<?php

namespace App\Middlewares;

use \Pecee\Http\Middleware\IMiddleware;
use \Pecee\Http\Request;
use App\Exceptions\E;
use Validate;
use Utils;
use View;

class CheckPostMiddleware implements IMiddleware
{
    public function handle(Request $request)
    {
        if (Utils::getValue('email', $_POST) != "") {
            if (!Validate::email($_POST['email'])) {
                View::json('邮箱或角色名格式错误', 3);
            }
            $_SESSION['auth_type'] = 'email';
        } elseif (Utils::getValue('username', $_POST) != "") {
            if (!Validate::playerName($_POST['username'])) {
                View::json('邮箱或角色名格式错误', 3);
            }
            $_SESSION['auth_type'] = 'username';
        } else {
            View::json('无效的参数', 3);
        }

        if ($request->getUri() == "/auth/forgot") return true;

        if (isset($_POST['password']) && $_POST['password'] != "") {
            return true;
        } else {
            View::json('密码不能为空哦', 2);
        }
    }
}
