<?php

namespace App\Http\Middleware;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DetectLanguagePrefer
{
    public function handle($request, \Closure $next)
    {
        $response = $next($request);

        if ($response instanceof Response) {
            $response->cookie('locale', config('app.locale'));
        }

        return $response;
    }

    public function detect(Request $request)
    {
        $locale = $request->input('lang') ?: ($request->cookie('locale') ?: $request->getPreferredLanguage());

        // If current locale is an alias of other locale
        if (($info = Arr::get(config('locales'), $locale)) && ($alias = Arr::get($info, 'alias'))) {
            $locale = $alias;
        }

        app()->setLocale($locale);
        AfterSessionBooted::$jobs[] = function () {
            session(['locale' => config('app.locale')]);
        };
    }

}
