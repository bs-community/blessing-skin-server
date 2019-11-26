<?php

namespace App\Http\Middleware;

use Cookie;
use Illuminate\Support\Arr;

class DetectLanguagePrefer
{
    public function handle($request, \Closure $next)
    {
        $locale = $request->input('lang')
            ?? $request->cookie('locale')
            ?? $request->getPreferredLanguage();
        if (
            ($info = Arr::get(config('locales'), $locale)) &&
            ($alias = Arr::get($info, 'alias'))
        ) {
            $locale = $alias;
        }
        $locale ?? app()->getLocale();
        if (! Arr::has(config('locales'), $locale)) {
            $locale = config('app.fallback_locale');
        }

        app()->setLocale($locale);
        Cookie::queue('locale', $locale);

        return $next($request);
    }
}
