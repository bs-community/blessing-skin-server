<?php

namespace App\Middlewares;

use Pecee\Http\Middleware\IMiddleware;
use Pecee\Http\Request;

class CheckAdminMiddleware implements IMiddleware
{
    public function handle(Request $request)
    {
        $user = (new CheckLoggedInMiddleware)->handle($request);

        if (!$user->is_admin) {
            \Http::redirect('../user', '看起来你并不是管理员哦');
        }
    }
}
