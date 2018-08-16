<?php

namespace App\Providers;

use Illuminate\Routing\Router;
use App\Events\ConfigureRoutes;
use App\Http\Middleware\CheckSessionUserValid;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function boot(Router $router)
    {
        //

        parent::boot($router);
    }

    /**
     * Define the routes for the application.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function map(Router $router)
    {
        $this->mapSetupRoutes($router);

        $this->mapStaticRoutes($router);

        $this->mapWebRoutes($router);

        event(new ConfigureRoutes($router));
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    protected function mapWebRoutes(Router $router)
    {
        $router->group([
            'middleware' => ['web', CheckSessionUserValid::class, 'csrf'],
            'namespace' => $this->namespace,
        ], function ($router) {
            require base_path('routes/web.php');
        });
    }

    /**
     * Define the "setup" routes for the application.
     *
     * The routes for setup wizard.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    protected function mapSetupRoutes(Router $router)
    {
        $router->group([
            'middleware' => 'web',
            'namespace' => $this->namespace,
        ], function ($router) {
            require base_path('routes/setup.php');
        });
    }

    /**
     * Define the "static" routes for the application.
     *
     * These routes will not load session, etc.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    protected function mapStaticRoutes(Router $router)
    {
        $router->group([
            'middleware' => 'static',
            'namespace' => $this->namespace,
        ], function ($router) {
            require base_path('routes/static.php');
        });
    }
}
