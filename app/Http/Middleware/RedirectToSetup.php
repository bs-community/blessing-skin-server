<?php

namespace App\Http\Middleware;

use Closure;
use Composer\Semver\Comparator;
use Illuminate\Filesystem\Filesystem;

class RedirectToSetup
{
    public function handle($request, Closure $next)
    {
        $version = config('app.version');
        if (! $request->is('setup*') && Comparator::greaterThan($version, option('version', $version))) {
            $user = $request->user();
            if ($user && $user->isAdmin()) {
                return redirect('/setup/update');
            } else {
                abort(503);
            }
        }

        $hasLock = resolve(Filesystem::class)->exists(storage_path('install.lock'));
        if ($hasLock || $request->is('setup*')) {
            return $next($request);
        }

        return redirect('/setup');
    }
}
