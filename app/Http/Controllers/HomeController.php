<?php

namespace App\Http\Controllers;

use Illuminate\Support\Arr;

class HomeController extends Controller
{
    public function index()
    {
        return view('home')
            ->with('user', auth()->user())
            ->with('site_description', option_localized('site_description'))
            ->with('transparent_navbar', (bool) option('transparent_navbar', false))
            ->with('fixed_bg', option('fixed_bg'))
            ->with('hide_intro', option('hide_intro'))
            ->with('home_pic_url', option('home_pic_url') ?: config('options.home_pic_url'));
    }

    public function apiRoot()
    {
        $copyright = Arr::get(
            [
                'Powered with ❤ by Blessing Skin Server.',
                'Powered by Blessing Skin Server.',
                'Proudly powered by Blessing Skin Server.',
                '由 Blessing Skin Server 强力驱动。',
                '自豪地采用 Blessing Skin Server。',
            ],
            option_localized('copyright_prefer', 0)
        );

        return response()->json([
            'blessing_skin' => config('app.version'),
            'spec' => 0,
            'copyright' => $copyright,
            'site_name' => option('site_name'),
        ]);
    }
}
