<?php

namespace App\Services;

class Application
{
    protected $base_dir;

    public function __construct($dir)
    {
        $this->base_dir = $dir;
    }

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

        // Load dotenv Configuration
        Boot::loadDotEnv($this->base_dir);

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

    public static function getVersion()
    {
        $config = require BASE_DIR."/config/app.php";
        return $config['version'];
    }
}
