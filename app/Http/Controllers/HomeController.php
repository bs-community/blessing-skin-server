<?php

namespace App\Http\Controllers;

use App\Models\User;
use Session;

class HomeController extends Controller
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

        $user = Session::has('uid') ? new User(session('uid')) : null;

        return view('index')->with('user', $user);
    }

}
