<?php

namespace App\Http\Controllers;

use Session;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Services\Repositories\UserRepository;

class HomeController extends Controller
{
    public function index(UserRepository $users, Request $request)
    {
        return view('index')->with('user', $users->getCurrentUser());
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
