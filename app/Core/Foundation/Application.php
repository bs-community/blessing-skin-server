<?php

namespace Blessing\Foundation;

use \Illuminate\Container\Container;
use \Blessing\Config;

class Application extends Container
{
    private $version = null;

    /**
     * Start Application
     *
     * @return void
     */
    public function run()
    {
        $this->boot();

        // Register Error Handler
        Boot::registerErrorHandler();

        // Redirect if not installed
        Boot::checkInstallation();

        // Start Route Dispatching
        Boot::bootRouter();
    }

    public function boot()
    {
        // Load Aliases
        Boot::loadServices();

        // Check Runtime Environment
        Boot::checkRuntimeEnv();

        // Register Facades
        Boot::registerFacades($this);

        // Set Default Timezone to UTC+8
        Boot::setTimeZone();

        // Load dotenv Configuration
        Boot::loadDotEnv(BASE_DIR);

        // Boot Eloquent ORM
        Boot::bootEloquent(Config::getDbConfig());

        // Start Session
        Boot::startSession();
    }

    /**
     * Get the version number of the application.
     *
     * @return string
     */
    public function version()
    {
        if (is_null($this->version)) {
            $config = require BASE_DIR."/config/app.php";
            $this->version = $config['version'];
        }
        return $this->version;
    }

}
