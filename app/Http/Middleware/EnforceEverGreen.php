<?php

namespace App\Http\Middleware;

use App\Exceptions\PrettyPageException;
use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class EnforceEverGreen
{
    public function handle($request, Closure $next)
    {
        $userAgent = $request->userAgent();

        preg_match('/Chrome\/(\d+)/', $userAgent, $matches);
        $isOldChrome = Arr::has($matches, 1) && $matches[1] < 55;

        if ($isOldChrome || Str::contains($userAgent, ['Trident', 'MSIE'])) {
            throw new PrettyPageException(trans('errors.http.ie'));
        }

        return $next($request);
    }
}
