<?php
/**
 * Setup Bootstraper of Blessing Skin Server
 */

// Define Base Directory
define('BASE_DIR', dirname(dirname(__DIR__)));

// Set Display Errors
@ini_set('display_errors', 'on');

// Register Composer Auto Loader
if (file_exists(BASE_DIR.'/vendor')) {
    require BASE_DIR.'/vendor/autoload.php';
} else {
    exit('错误：/vendor 文件夹不存在');
}

// Register Helpers
require BASE_DIR.'/vendor/laravel/framework/src/Illuminate/Foundation/helpers.php';
require __DIR__."/helpers.php";

// Load dotenv Configuration
if (file_exists(BASE_DIR."/.env")) {
    $dotenv = new \Dotenv\Dotenv(BASE_DIR);
    $dotenv->load();
} else {
    exit('错误：.env 配置文件不存在');
}

if (false === menv('APP_KEY', false)) {
    exit('错误：.env 已过期，请重新复制一份 .env.example 并修改配置');
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
(new Devitek\Core\Translation\TranslationServiceProvider($app))->register();

$request = Symfony\Component\HttpFoundation\Request::createFromGlobals();

$request = (new Illuminate\Http\Request)->duplicate(
    $request->query->all(), $request->request->all(), $request->attributes->all(),
    // quick fix: replace request URI with empty string
    $request->cookies->all(), $request->files->all(), array_replace($request->server->all(), ['REQUEST_URI' => ''])
);

// Enable URL generator
$app->bind('url', function ($app) {
    $routes = $app['router']->getRoutes();
    $app->instance('routes', $routes);

    $url = new Illuminate\Routing\UrlGenerator(
        $routes, $app['request']
    );

    return $url;
});

$app->instance('request', $request);

$app->singleton('database', App\Services\Database\Database::class);
$app->singleton('option',   App\Services\Repositories\OptionRepository::class);

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

if (isset($_COOKIE['BS_SESSION'])) {
    $session->setId($encrypter->decrypt($_COOKIE['BS_SESSION']));
}

$session->start();
