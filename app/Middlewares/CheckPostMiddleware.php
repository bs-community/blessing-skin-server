<?php

namespace App\Middlewares;

use \Pecee\Http\Middleware\IMiddleware;
use \Pecee\Http\Request;
use App\Exceptions\E;
use Validate;
use View;

class CheckPostMiddleware implements IMiddleware
{
    public function handle(Request $request)
    {
        if (isset($_POST['email']) && $_POST['email'] != "") {
            if (!Validate::email($_POST['email'])) {
                View::json('邮箱格式错误', 3);
            }

            if ($request->getUri() == "/auth/forgot") return true;

            if (isset($_POST['nickname']) && ($_POST['nickname'] != \Utils::convertString($_POST['nickname'])))
                View::json('无效的昵称，昵称不能包含奇怪的字符', 1);

            if (isset($_POST['password']) && $_POST['password'] != "") {
                return true;
            } else {
                View::json('密码不能为空哦', 2);
            }
        } else {
            View::json('邮箱地址不能为空哦', 3);
        }
    }
}
