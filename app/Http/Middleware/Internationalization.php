<?php

namespace App\Http\Middleware;

use App;
use Session;
use Illuminate\Support\Arr;

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
