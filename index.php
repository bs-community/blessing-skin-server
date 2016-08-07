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

// Check Runtime Environment
Boot::checkRuntimeEnv();

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
Boot::run();
