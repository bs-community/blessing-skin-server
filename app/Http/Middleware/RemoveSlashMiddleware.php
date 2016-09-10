<?php

namespace App\Http\Middleware;

class RemoveSlashMiddleware
{
    public function handle($request, \Closure $next)
    {
        if (substr($request->getRequestUri(), -1) == "/") {
            $base_url = $request->getBaseUrl();
            // try to remove slash at the end of current url
            $new_url  = substr($request->getRequestUri(), 0, -1);

            if ($new_url != $base_url) {
                return redirect(str_replace($base_url, '', $new_url));
            }
        }

        return $next($request);
    }
}
