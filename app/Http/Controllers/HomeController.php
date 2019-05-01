<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    public function index()
    {
        return view('index')->with('user', auth()->user())
            ->with('transparent_navbar', option('transparent_navbar', false))
            ->with('home_pic_url', option('home_pic_url') ?: config('options.home_pic_url'));
    }

    public function apiRoot()
    {
        return response()->json([
            'blessing_skin' => config('app.version'),
            'spec' => 0,
            'copyright' => bs_copyright(),
            'site_name' => option('site_name'),
        ]);
    }
}
