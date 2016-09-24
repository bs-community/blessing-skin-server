<?php

namespace App\Http\Controllers;

use Session;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;

class HomeController extends Controller
{

    public function index()
    {
        if (isset($_COOKIE['uid']) && isset($_COOKIE['token'])) {
            $user = new User($_COOKIE['uid']);

            if ($_COOKIE['token'] != $user->getToken() || $user->getPermission() == "-1") {
                // delete cookies
                setcookie("uid",   "", time() - 3600, '/');
                setcookie("token", "", time() - 3600, '/');
            }
        }

        $user = Session::has('uid') ? new User(session('uid')) : null;

        return view('index')->with('user', $user);
    }

    public function locale($lang, Request $request)
    {
        if (Arr::exists(config('locales'), $lang)) {
            Session::set('locale', $lang);
        }

        if (isset($_SERVER['HTTP_REFERER'])) {
            return redirect('/')->setTargetUrl($_SERVER['HTTP_REFERER'])->withCookie('locale', $lang);
        } else {
            return redirect('/')->withCookie('locale', $lang);
        }
    }

}
