<?php
/**
 * Setup Bootstraper of Blessing Skin Server
 */

// Define Base Directory
define('BASE_DIR', dirname(dirname(__FILE__)));

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

// Register Basic Service Providers manually
(new Illuminate\View\ViewServiceProvider($app))->register();
(new Illuminate\Foundation\Bootstrap\LoadConfiguration)->bootstrap($app);
(new Illuminate\Database\DatabaseServiceProvider($app))->register();
(new Illuminate\Filesystem\FilesystemServiceProvider($app))->register();

$app['url'] = $app->share(function ($app) {
    $routes = $app['router']->getRoutes();

    // The URL generator needs the route collection that exists on the router.
    // Keep in mind this is an object, so we're passing by references here
    // and all the registered routes will be available to the generator.
    $app->instance('routes', $routes);

    $request = Symfony\Component\HttpFoundation\Request::createFromGlobals();

    $request = (new Illuminate\Http\Request)->duplicate(
        $request->query->all(), $request->request->all(), $request->attributes->all(),
        // quick fix: replace request URI with empty string
        $request->cookies->all(), $request->files->all(), array_replace($request->server->all(), ['REQUEST_URI' => ''])
    );

    $url = new Illuminate\Routing\UrlGenerator(
        $routes, $request
    );

    return $url;
});

$app->singleton('database', App\Services\Database\Database::class);
$app->singleton('option',   App\Services\OptionRepository::class);

require BASE_DIR.'/vendor/laravel/framework/src/Illuminate/Foundation/helpers.php';

// Load Aliases
$config = require BASE_DIR.'/config/app.php';

foreach ($config['aliases'] as $facade => $class) {
    class_alias($class, $facade);
}

View::addExtension('tpl', 'blade');

$config = require BASE_DIR.'/config/database.php';

$db_config = $config['connections']['mysql'];

// Check Database Config
@$conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database'], $db_config['port']);

if ($conn->connect_error) {
    throw new App\Exceptions\E("无法连接至 MySQL 服务器，请检查你的配置：".$conn->connect_error, $conn->connect_errno, true);
}

// Boot Eloquent ORM
$capsule = new Illuminate\Database\Capsule\Manager;
$capsule->addConnection($db_config);
$capsule->setAsGlobal();
$capsule->bootEloquent();

// Start Session
session_start();

function checkTableExist() {
    $tables = ['users', 'closets', 'players', 'textures', 'options'];

    foreach ($tables as $table_name) {
        // prefix will be added automatically
        if (!Database::hasTable($table_name)) {
            return false;
        }
    }

    return true;
}

function redirect_to($url, $msg = "") {
    if ($msg !== "") {
        if (app()->bound('session')) {
            Session::flash('msg', $msg);
            Session::save();
        } else {
            $_SESSION['msg'] = $msg;
        }
    }

    if (!headers_sent()) {
        header('Location: '.$url);
    } else {
        echo "<meta http-equiv='Refresh' content='0; URL=$url'>";
    }
    exit;
}

/**
 * Check POST values in a simple way
 *
 * @param  array  $keys
 * @return void
 */
function check_post(Array $keys) {
    foreach ($keys as $key) {
        if (!isset($_POST[$key])) {
            return false;
        }
    }
    return true;
}

function check_password($password)
{
    if (strlen($password) > 16 || strlen($password) < 8) {
        return false;
    }
    return true;
}
