<?php
/**
 * Setup Bootstraper of Blessing Skin Server
 */

// Define Base Directory
define('BASE_DIR', dirname(dirname(__DIR__)));

// Register Composer Auto Loader
require BASE_DIR.'/vendor/autoload.php';

// Load dotenv Configuration
if (file_exists(BASE_DIR."/.env")) {
    $dotenv = new \Dotenv\Dotenv(BASE_DIR);
    $dotenv->load();
} else {
    exit('错误：.env 配置文件不存在');
}

// Register Error Hanlders
$whoops = new \Whoops\Run;
$handler = new \Whoops\Handler\PrettyPageHandler;
$whoops->pushHandler($handler);
$whoops->register();

// Instantiate Application
$app = new Illuminate\Foundation\Application(BASE_DIR);

// Set Container for Facades
Illuminate\Support\Facades\Facade::setFacadeApplication($app);

// Load Aliases
$config = require BASE_DIR.'/config/app.php';

foreach ($config['aliases'] as $facade => $class) {
    class_alias($class, $facade);
}

// Register Basic Service Providers manually
(new Illuminate\View\ViewServiceProvider($app))->register();
(new Illuminate\Foundation\Bootstrap\LoadConfiguration)->bootstrap($app);
(new Illuminate\Database\DatabaseServiceProvider($app))->register();
(new Illuminate\Filesystem\FilesystemServiceProvider($app))->register();
(new Illuminate\Session\SessionServiceProvider($app))->register();
(new Illuminate\Encryption\EncryptionServiceProvider($app))->register();

$request = Symfony\Component\HttpFoundation\Request::createFromGlobals();

$request = (new Illuminate\Http\Request)->duplicate(
    $request->query->all(), $request->request->all(), $request->attributes->all(),
    // quick fix: replace request URI with empty string
    $request->cookies->all(), $request->files->all(), array_replace($request->server->all(), ['REQUEST_URI' => ''])
);

$app->bind('url', function ($app) {
    $routes = $app['router']->getRoutes();

    // The URL generator needs the route collection that exists on the router.
    // Keep in mind this is an object, so we're passing by references here
    // and all the registered routes will be available to the generator.
    $app->instance('routes', $routes);

    $url = new Illuminate\Routing\UrlGenerator(
        $routes, $app['request']
    );

    return $url;
});

$app->instance('request', $request);

$app->singleton('database', App\Services\Database\Database::class);
$app->singleton('option',   App\Services\OptionRepository::class);

require BASE_DIR.'/vendor/laravel/framework/src/Illuminate/Foundation/helpers.php';
require __DIR__."/helpers.php";


View::addExtension('tpl', 'blade');

$db_config = get_db_config();

// Check Database Config
check_db_config($db_config);

// Boot Eloquent ORM
$capsule = new Illuminate\Database\Capsule\Manager;
$capsule->addConnection($db_config);
$capsule->setAsGlobal();
$capsule->bootEloquent();

// Start Session
session_start();
// start laravel session
$encrypter = $app->make('Illuminate\Contracts\Encryption\Encrypter');
$session = $app->make('session')->driver();
$session->setId($encrypter->decrypt($_COOKIE['bs_session']));
$session->start();
