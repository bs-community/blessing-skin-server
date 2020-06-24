<?php

namespace App\Http\Middleware;

use Illuminate\Filesystem\Filesystem;

class CheckInstallation
{
    public function handle($request, \Closure $next)
    {
        $hasLock = resolve(Filesystem::class)->exists(storage_path('install.lock'));
        if ($hasLock) {
            return response()->view('setup.locked');
        }

        return $next($request);
    }
}
