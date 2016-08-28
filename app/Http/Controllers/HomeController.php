<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use App\Models\User;
use Session;

class HomeController extends BaseController
{

    public function index()
    {
        if (isset($_COOKIE['uid']) && isset($_COOKIE['token'])) {
            $user = new User($_COOKIE['uid']);

            if ($_COOKIE['token'] == $user->getToken() && $user->getPermission() != "-1") {
                Session::put('uid'  , $_COOKIE['uid']);
                Session::put('token', $_COOKIE['token']);
            } else {
                // delete cookies
                setcookie("uid",   "", time() - 3600, '/');
                setcookie("token", "", time() - 3600, '/');
            }
        }

        $user = session()->has('uid') ? new User(session('uid')) : null;

        echo \View::make('index')->with('user', $user);
    }

}
