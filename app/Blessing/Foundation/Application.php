<?php

namespace Blessing\Foundation;

use \Illuminate\Container\Container;
use \App\Services\Config;

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

        // Register Error Handler
        Boot::registerErrorHandler();

        // Boot Eloquent ORM
        Boot::bootEloquent(Config::getDbConfig());

        // Redirect if not installed
        Boot::checkInstallation();

        // Start Session
        Boot::startSession();

        // Start Route Dispatching
        Boot::bootRouter();
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
