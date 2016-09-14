<?php

namespace App\Http\Middleware;

use Illuminate\Support\Arr;
use Session;
use App;

class Internationalization
{
    public function handle($request, \Closure $next)
    {
        if (Session::has('locale') && Arr::exists(config('locales'), session('locale'))) {
            App::setLocale(Session::get('locale'));
        }

        return $next($request);
    }
}
