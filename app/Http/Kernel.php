<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\TrimStrings::class,
        \App\Http\Middleware\ConvertEmptyStringsToNull::class,
        \App\Http\Middleware\DetectLanguagePrefer::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            \App\Http\Middleware\EnforceEverGreen::class,
            \App\Http\Middleware\RedirectToSetup::class,
            'bindings',
        ],

        'api' => [
            'bindings',
        ],

        'authorize' => [
            'auth:web',
            \App\Http\Middleware\RejectBannedUser::class,
            \App\Http\Middleware\EnsureEmailFilled::class,
            \App\Http\Middleware\FireUserAuthenticated::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'role' => \App\Http\Middleware\CheckRole::class,
        'setup' => \App\Http\Middleware\CheckInstallation::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \App\Http\Middleware\CheckUserVerified::class,
        'scope' => \Laravel\Passport\Http\Middleware\CheckForAnyScope::class,
        'scopes' => \Laravel\Passport\Http\Middleware\CheckScopes::class,
    ];
}
