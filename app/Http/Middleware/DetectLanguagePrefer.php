<?php

namespace App\Http\Middleware;

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

        // For Microsoft Edge and IE
        if ($locale == 'zh_HANS_CN') {
            $locale = 'zh_CN';
        }

        // If current locale is an alias of other locale
        if (($info = array_get(config('locales'), $locale)) && ($alias = array_get($info, 'alias'))) {
            $locale = $alias;
        }

        app()->setLocale($locale);
        AfterSessionBooted::$jobs[] = function () {
            session(['locale' => config('app.locale')]);
        };
    }

}
