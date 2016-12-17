<?php

namespace App\Http\Middleware;

use Illuminate\Support\Str;

class RedirectIfUrlEndsWithSlash
{
    public function handle($request, \Closure $next)
    {
        if (substr($request->getRequestUri(), -1) == '/') {
            $baseUrl = $request->getBaseUrl();

            // try to remove slash at the end of current url
            $newUrl  = substr($request->getRequestUri(), 0, -1);

            if ($newUrl != $baseUrl) {
                return redirect(Str::replaceLast($baseUrl, '', $newUrl));
            }
        }

        return $next($request);
    }
}
