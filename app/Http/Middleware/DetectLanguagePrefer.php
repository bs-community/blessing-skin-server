<?php

namespace App\Http\Middleware;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DetectLanguagePrefer
{
    public function handle($request, \Closure $next)
    {
        $locale = $request->input('lang') ?? session('locale') ?? $request->getPreferredLanguage();
        if (($info = Arr::get(config('locales'), $locale)) && ($alias = Arr::get($info, 'alias'))) {
            $locale = $alias;
        }
        app()->setLocale($locale);
        session(['locale' => $locale]);

        return $next($request);
    }
}
