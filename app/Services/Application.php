<?php

namespace App\Services;

class Application
{
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
     * Get current app version
     *
     * @return string
     */
    public static function getVersion()
    {
        $config = require BASE_DIR."/config/app.php";
        return $config['version'];
    }

}
