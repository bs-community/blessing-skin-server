<?php

namespace App\Http\Middleware;

use Closure;

class AfterSessionBooted
{
    /**
     * Jobs should be done after session booted.
     *
     * @var array
     */
    static $jobs;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        foreach (static::$jobs as $job) {
            if (is_callable($job)) {
                app()->call($job);
            }
        }

        return $next($request);
    }
}
