<?php
/**
 * Bootstrap file of Blessing Skin Server
 */

// Define Base Directory
define('BASE_DIR', __DIR__);

// Register Composer Auto Loader
require BASE_DIR.'/vendor/autoload.php';

// Load Aliases
App\Services\Boot::loadServices();

// Load dotenv Configuration
Boot::loadDotEnv();

// Register Error Handler
Boot::registerErrorHandler();

$db_config = Config::getDbConfig();

// Boot Eloquent ORM
if (Config::checkDbConfig($db_config)) {
    Boot::bootEloquent($db_config);
}

// Redirect to Setup Page
if (!Config::checkTableExist($db_config)) {
    Http::redirect('../setup/index.php');
}

Config::checkFolderExist();

// Start Session
Boot::startSession();

// Start Route Dispatching
Boot::run();
