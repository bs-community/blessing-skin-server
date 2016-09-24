<?php

namespace App\Http\Middleware;

use App;
use Cookie;
use Session;
use Illuminate\Support\Arr;

class Internationalization
{
    public function handle($request, \Closure $next)
    {
        // Load from cookie
        if (Cookie::has('locale')) {
            session(['locale' => Cookie::get('locale')]);
        }

        if (Session::has('locale') && Arr::exists(config('locales'), session('locale'))) {
            // Set app locale dynamically
            App::setLocale(session('locale'));
        }

        return $next($request);
    }
}
