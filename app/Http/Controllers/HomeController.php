<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    public function index()
    {
        return view('index')->with('user', auth()->user())
            ->with('home_pic_url', option('home_pic_url') ?: config('options.home_pic_url'));
    }
}
