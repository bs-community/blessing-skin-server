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
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\ForbiddenIE::class,
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
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
        'auth'        => \App\Http\Middleware\Authenticate::class,
        'bindings'    => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'verified'    => \App\Http\Middleware\CheckUserVerified::class,
        'guest'       => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'admin'       => \App\Http\Middleware\CheckAdministrator::class,
        'super-admin' => \App\Http\Middleware\CheckSuperAdmin::class,
        'player'      => \App\Http\Middleware\CheckPlayerExist::class,
        'setup'       => \App\Http\Middleware\CheckInstallation::class,
        'signed'      => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle'    => \Illuminate\Routing\Middleware\ThrottleRequests::class,
    ];
}
