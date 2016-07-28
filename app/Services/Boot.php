<?php

namespace App\Services;

use \Illuminate\Database\Capsule\Manager as Capsule;
use \Pecee\SimpleRouter\SimpleRouter as Router;

class Boot
{
    public static function loadDotEnv()
    {
        if (Config::checkDotEnvExist()) {
            $dotenv = new \Dotenv\Dotenv(BASE_DIR);
            $dotenv->load();
        }
    }

    public static function loadServices()
    {
        // Set Aliases for App\Services
        $services = require BASE_DIR.'/config/services.php';

        foreach ($services as $facade => $class) {
            class_alias($class, $facade);
        }
    }

    public static function registerErrorHandler()
    {
        if (!isset($_ENV))
            self::loadDotEnv();

        if ($_ENV['APP_DEBUG'] !== "false") {
            // whoops: php errors for cool kids
            $whoops = new \Whoops\Run;
            $handler = ($_SERVER['REQUEST_METHOD'] == "GET") ?
                new \Whoops\Handler\PrettyPageHandler : new \Whoops\Handler\PlainTextHandler;
            $whoops->pushHandler($handler);
            $whoops->register();
        } else {
            // Register custom error handler
            App\Exceptions\ExceptionHandler::register();
        }
    }

    public static function bootEloquent(Array $config)
    {
        $capsule = new Capsule;
        $capsule->addConnection($config);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }

    public static function startSession()
    {
        session_start();
    }

    public static function bootRouter()
    {
        /**
         * URL ends with slash will cause many reference problems
         */
        if (Http::getUri() != "/" && substr(Http::getUri(), -1) == "/") {
            $url = substr(Http::getCurrentUrl(), 0, -1);
            Http::redirect($url);
        }

        // Require Route Config
        Router::group([
            'exceptionHandler' => 'App\Exceptions\RouterExceptionHandler'
        ], function() {
            require BASE_DIR.'/config/routes.php';
        });
    }

    public static function run()
    {
        self::bootRouter();
        // Start Route Dispatching
        Router::start('App\Controllers');
    }
}
