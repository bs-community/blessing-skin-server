<?php

namespace App\Controllers;

use App\Models\User;

class HomeController extends BaseController
{

    public function index()
    {
        if (isset($_COOKIE['email']) && isset($_COOKIE['token'])) {
            $user = new User($_COOKIE['email']);

            if ($_COOKIE['token'] == $user->getToken() && $user->getPermission() != "-1") {
                $_SESSION['email'] = $_COOKIE['email'];
                $_SESSION['token'] = $_COOKIE['token'];
            } else {
                // delete cookies
                setcookie("email", "", time() - 3600, '/');
                setcookie("token", "", time() - 3600, '/');
            }
        }

        $user = isset($_SESSION['email']) ? new User($_SESSION['email']) : null;

        echo \View::make('index')->with('user', $user);
    }

}
