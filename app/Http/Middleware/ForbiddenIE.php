<?php

namespace App\Http\Middleware;

use App\Exceptions\PrettyPageException;
use Closure;
use Illuminate\Support\Str;

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
