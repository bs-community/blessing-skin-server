<?php

namespace App\Controllers;

use App\Models\User;

class HomeController extends BaseController
{

    public function index()
    {
        if (isset($_COOKIE['uid']) && isset($_COOKIE['token'])) {
            $user = new User($_COOKIE['uid']);

            if ($_COOKIE['token'] == $user->getToken() && $user->getPermission() != "-1") {
                $_SESSION['uid'] = $_COOKIE['uid'];
                $_SESSION['token'] = $_COOKIE['token'];
            } else {
                // delete cookies
                setcookie("uid",   "", time() - 3600, '/');
                setcookie("token", "", time() - 3600, '/');
            }
        }

        $user = isset($_SESSION['uid']) ? new User($_SESSION['uid']) : null;

        echo \View::make('index')->with('user', $user);
    }

}
