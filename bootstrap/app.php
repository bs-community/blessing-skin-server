<?php

use App\Console\Commands;
use App\Exceptions\Handler;
use App\Http\Middleware;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware as MiddlewareConfig;

/*
 * Routing is not declared here. App\Providers\RouteServiceProvider maps the
 * "static", "web" and "api" route files, applies middleware to Passport's
 * routes and fires the ConfigureRoutes event that plugins listen to, none of
 * which withRouting() can express.
 */
$app = Application::configure(basePath: dirname(__DIR__))
    ->withMiddleware(function (MiddlewareConfig $middleware) {
        /*
         * The global stack is declared with use() rather than assembled from
         * Laravel's defaults with append()/remove(): this application never
         * ran TrustProxies, HandleCors or ValidatePathEncoding, and pulling
         * them in here would be a behaviour change rather than a port.
         */
        $middleware->use([
            \Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class,
            \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
            \Illuminate\Foundation\Http\Middleware\TrimStrings::class,
            Middleware\ConvertEmptyStringsToNull::class,
            Middleware\DetectLanguagePrefer::class,
        ]);

        $middleware->group('web', [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            Middleware\EnforceEverGreen::class,
            Middleware\RedirectToSetup::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        $middleware->group('api', [
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        $middleware->group('authorize', [
            'auth:web',
            Middleware\RejectBannedUser::class,
            Middleware\EnsureEmailFilled::class,
            Middleware\FireUserAuthenticated::class,
        ]);

        $middleware->alias([
            'auth' => Middleware\Authenticate::class,
            'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
            'guest' => Middleware\RedirectIfAuthenticated::class,
            'role' => Middleware\CheckRole::class,
            'setup' => Middleware\CheckInstallation::class,
            'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
            'verified' => Middleware\CheckUserVerified::class,
            'scope' => \Laravel\Passport\Http\Middleware\CheckTokenForAnyScope::class,
            'scopes' => \Laravel\Passport\Http\Middleware\CheckToken::class,
        ]);
    })
    ->withCommands([
        \Laravel\Passport\Console\KeysCommand::class,
        Commands\BsInstallCommand::class,
        Commands\OptionsCacheCommand::class,
        Commands\PluginDisableCommand::class,
        Commands\PluginEnableCommand::class,
        Commands\SaltRandomCommand::class,
        Commands\UpdateCommand::class,
    ])
    ->withExceptions()
    ->create();

/*
 * App\Exceptions\Handler overrides convertExceptionToArray() to emit the
 * trimmed, plugin-aware trace this application's JSON API returns. Laravel's
 * Exceptions configuration object exposes report/render/respond hooks but
 * nothing for that method, so the handler stays a real class and is bound
 * over the default one here.
 */
$app->singleton(ExceptionHandler::class, Handler::class);

return $app;
