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
        $hasLock = resolve(Filesystem::class)->exists(storage_path('install.lock'));

        // If lock isn't existed, it means that BS isn't installed.
        // Database is unavailable at this time, so we should disable the loader.
        if (!$hasLock) {
            config(['translation-loader.translation_loaders' => []]);
        }

        if ($hasLock && !$request->is('setup*') && Comparator::greaterThan($version, option('version', $version))) {
            $user = $request->user();
            if ($user && $user->isAdmin()) {
                return redirect('/setup/update');
            } else {
                abort(503);
            }
        }

        if ($hasLock || $request->is('setup*')) {
            return $next($request);
        }

        return redirect('/setup');
    }
}
