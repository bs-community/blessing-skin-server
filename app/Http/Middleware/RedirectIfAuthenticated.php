<?php

namespace App\Http\Middleware;

use App;
use Session;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    public function handle($request, \Closure $next)
    {
        return Auth::check() ? redirect('user') : $next($request);
    }
}
