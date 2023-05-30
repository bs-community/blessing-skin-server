<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;

class DetectLanguagePrefer
{
    public function handle(Request $request, \Closure $next)
    {
        $locale = $request->input('lang')
            ?? $request->cookie('locale')
            ?? $request->getPreferredLanguage();
        if (
            ($info = Arr::get(config('locales'), $locale))
            && ($alias = Arr::get($info, 'alias'))
        ) {
            $locale = $alias;
        }
        $locale ?? app()->getLocale();
        if (!Arr::has(config('locales'), $locale)) {
            $locale = config('app.fallback_locale');
        }

        app()->setLocale($locale);

        /** @var Response */
        $response = $next($request);
        $response->cookie('locale', $locale, 120);

        return $response;
    }
}
