<?php

namespace App\Http\Middleware;

use Cookie;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DetectLanguagePrefer
{
    public function handle($request, \Closure $next)
    {
        $locale = $request->input('lang') ?? $request->cookie('locale') ?? $request->getPreferredLanguage();
        if (($info = Arr::get(config('locales'), $locale)) && ($alias = Arr::get($info, 'alias'))) {
            $locale = $alias;
        }
        app()->setLocale($locale);
        Cookie::queue('locale', $locale);
        return $next($request);
    }
}
