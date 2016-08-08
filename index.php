<?php
/**
 * Bootstrap file of Blessing Skin Server
 */

// Define Base Directory
define('BASE_DIR', __DIR__);

// Register Composer Auto Loader
require BASE_DIR.'/vendor/autoload.php';

// Initialize Application
$app = new App\Services\Application(BASE_DIR);

// Start Application
$app->run();
