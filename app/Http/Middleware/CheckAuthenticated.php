<?php

namespace App\Http\Middleware;

use App;
use Closure;
use Session;
use App\Models\User;
use App\Events\UserAuthenticated;
use Illuminate\Support\Facades\Auth;

class CheckAuthenticated
{
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->permission == User::BANNED) {
                Auth::logout();

                abort(403, trans('auth.check.banned'));
            }

            // Ask for filling email
            if ($user->email == '') {
                return $this->askForFillingEmail($request, $next);
            }

            event(new UserAuthenticated($user));

            return $next($request);
        } else {
            $this->flashLastRequestedPath();

            return redirect('auth/login')->with('msg', trans('auth.check.anonymous'));
        }
    }

    public function askForFillingEmail($request, Closure $next)
    {
        $user = Auth::user();

        if (isset($request->email)) {
            if (filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
                if (User::where('email', $request->email)->get()->isEmpty()) {
                    $user->setEmail($request->email);

                    return $next($request);
                } else {
                    return response()->view('auth.bind', ['msg' => trans('auth.bind.registered')]);
                }
            } else {
                return response()->view('auth.bind', ['msg' => trans('auth.validation.email')]);
            }
        }

        return response()->view('auth.bind');
    }

    protected function flashLastRequestedPath($path = null)
    {
        $path = $path ?: app('request')->path();

        return session(['last_requested_path' => $path]);
    }
}
