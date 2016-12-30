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

        if (Session::has('locale')) {
            // Set app locale dynamically
            App::setLocale(session('locale'));
        } else {
            App::setLocale($request->getPreferredLanguage());
        }

        return $next($request);
    }
}
