<?php

/**
 * Entrance of Blessing Skin Server
 *
 * @package  Blessing Skin Server
 * @author   printempw <h@prinzeugen.net>
 */

define('BASE_DIR', __DIR__);

require BASE_DIR.'/bootstrap/autoload.php';

$app = require_once BASE_DIR.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);
