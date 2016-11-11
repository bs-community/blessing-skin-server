<?php

namespace App\Http\Middleware;

use Illuminate\Support\Str;

class RemoveSlashMiddleware
{
    public function handle($request, \Closure $next)
    {
        if (substr($request->getRequestUri(), -1) == "/") {
            $base_dir = $request->getBaseUrl();
            // try to remove slash at the end of current url
            $new_url  = substr($request->getRequestUri(), 0, -1);

            if ($new_url != $base_dir) {
                return redirect(Str::replaceLast($base_dir, '', $new_url));
            }
        }

        return $next($request);
    }
}
