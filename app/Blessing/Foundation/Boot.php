<?php

namespace Blessing\Foundation;

use \Illuminate\Database\Capsule\Manager as Capsule;
use \Illuminate\Support\Facades\Facade;
use \Pecee\SimpleRouter\SimpleRouter as Router;
use \App\Exceptions\ExceptionHandler;
use \App\Exceptions\E;
use \App\Services\Config;
use \App\Services\Http;

class Boot
{
    public static function loadDotEnv($dir)
    {
        if (Config::checkDotEnvExist()) {
            $dotenv = new \Dotenv\Dotenv($dir);
            $dotenv->load();
        }
    }

    public static function registerFacades(Application $app)
    {
        Facade::setFacadeApplication($app);

        $app->instance('app', $app);
        $app->bind('manager', \App\Services\PluginManager::class);
        $app->bind('db', \Blessing\Foundation\Database::class);
    }

    public static function setTimeZone($timezone = 'Asia/Shanghai')
    {
        // set default time zone, UTC+8 for default
        date_default_timezone_set($timezone);
    }

    public static function checkRuntimeEnv()
    {
        Config::checkPHPVersion();
        Config::checkCache();
    }

    public static function checkInstallation($redirect_to = '../setup/index.php')
    {
        if (!Config::checkTableExist()) {
            Http::redirect($redirect_to);
        }

        if (!is_dir(BASE_DIR.'/textures/')) {
            throw new E("检测到 `textures` 文件夹已被删除，请重新运行 <a href='./setup'>安装程序</a>，或者手动放置一个。", -1, true);
        }

        if (\App::version() != \App\Services\Option::get('version', '')) {
            Http::redirect(Http::getBaseUrl().'/setup/update.php');
            exit;
        }

        return true;
    }

    public static function loadServices()
    {
        // Set Aliases for App\Services
        $services = require BASE_DIR.'/config/services.php';

        foreach ($services as $facade => $class) {
            class_alias($class, $facade);
        }
    }

    /**
     * Register error handler
     *
     * @param  object $handler Push specified whoops handler
     * @return void
     */
    public static function registerErrorHandler($handler = null)
    {
        if (!is_null($handler) && $handler instanceof \Whoops\Handler\HandlerInterface) {
            $whoops = new \Whoops\Run;
            $whoops->pushHandler($handler);
            $whoops->register();
            return;
        }

        if ($_ENV['APP_DEBUG'] !== "false") {
            // whoops: php errors for cool kids
            $whoops = new \Whoops\Run;
            $handler = ($_SERVER['REQUEST_METHOD'] == "GET") ?
                new \Whoops\Handler\PrettyPageHandler : new \Whoops\Handler\PlainTextHandler;
            $whoops->pushHandler($handler);
            $whoops->register();
        } else {
            // Register custom error handler
            ExceptionHandler::register();
        }
    }

    public static function bootEloquent(Array $config)
    {
        if (Config::checkDbConfig($config)) {
            $capsule = new Capsule;
            $capsule->addConnection($config);
            $capsule->setAsGlobal();
            $capsule->bootEloquent();
        }
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

        // Start Route Dispatching
        Router::start('App\Controllers');
    }
}
