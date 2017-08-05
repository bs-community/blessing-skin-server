<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Repositories\UserRepository;

class HomeController extends Controller
{
    public function index(UserRepository $users, Request $request)
    {
        return view('index')->with('user', $users->getCurrentUser())
            ->with('home_pic_url', option('home_pic_url') ?: config('options.home_pic_url'));
    }

}
