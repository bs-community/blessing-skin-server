<?php

namespace App\Http\Middleware;

use App\Http\Controllers\SetupController;

class CheckInstallation
{
    public function handle($request, \Closure $next)
    {
        if (SetupController::checkTablesExist()) {
            return response()->view('setup.locked');
        }

        return $next($request);
    }
}
