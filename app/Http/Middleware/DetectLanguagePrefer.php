<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;

class DetectLanguagePrefer
{
    public function handle(Request $request, \Closure $next)
    {
        $available = config('locales', []);

        $locale = $request->input('lang')
            ?? $request->cookie('locale')
            ?? $request->getPreferredLanguage();

        // Match a literal key, not a dot path. Arr::has() would resolve
        // "zh_CN.name" to config('locales')['zh_CN']['name'] and accept it,
        // which then breaks every page render (see #684).
        if (is_string($locale) && array_key_exists($locale, $available)) {
            $info = $available[$locale];
            $alias = is_array($info) ? Arr::get($info, 'alias') : null;

            if (is_string($alias) && array_key_exists($alias, $available)) {
                $locale = $alias;
            }
        } else {
            $locale = config('app.fallback_locale');
        }

        app()->setLocale($locale);

        /** @var Response */
        $response = $next($request);
        if (!in_array('api', optional($request->route())->middleware() ?? [])) {
            $response->cookie('locale', $locale, 120);
        }

        return $response;
    }
}
