<?php
/**
 * Bootstrap file of Blessing Skin Server
 */
namespace App;

// BASE_DIR
define('BASE_DIR', __DIR__);

// Autoloader
require BASE_DIR.'/vendor/autoload.php';

// Load dotenv configuration
$dotenv = new \Dotenv\Dotenv(BASE_DIR);
$dotenv->load();

define('SALT', $_ENV['SALT']);

if ($_ENV['APP_DEBUG'] !== "false") {
    // whoops: php errors for cool kids
    $whoops = new \Whoops\Run;
    $handler = ($_SERVER['REQUEST_METHOD'] == "GET") ?
        new \Whoops\Handler\PrettyPageHandler : new \Whoops\Handler\PlainTextHandler;
    $whoops->pushHandler($handler);
    $whoops->register();
}

$services = require BASE_DIR.'/config/services.php';
foreach ($services as $facade => $class) {
    class_alias($class, $facade);
}

// Check database config
$db_config = require BASE_DIR.'/config/database.php';
\Database::checkConfig($db_config);

// Boot Eloquent ORM
$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($db_config);
$capsule->bootEloquent();

session_start();

\Pecee\SimpleRouter\SimpleRouter::group([
    'exceptionHandler' => 'App\Exceptions\RouterExceptionHandler',
    'middleware'       => 'App\Middlewares\RemoveUrlSlashMiddleware'
], function() {
    require BASE_DIR.'/config/routes.php';
});

// Start route dispatching
\Pecee\SimpleRouter\SimpleRouter::start('App\Controllers');
