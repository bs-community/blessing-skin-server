<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Str;
use App\Exceptions\PrettyPageException;

class ForbiddenIE
{
    public function handle($request, Closure $next)
    {
        if (Str::contains($request->userAgent(), ['Trident', 'MSIE'])) {
            throw new PrettyPageException(trans('errors.http.ie'));
        }

        return $next($request);
    }
}
