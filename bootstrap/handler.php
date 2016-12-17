<?php

/*
|--------------------------------------------------------------------------
| Handle The Request
|--------------------------------------------------------------------------
|
| Blessing Skin Server separated these codes here to ensure that
| runtime check at index.php will be executed correctly, since
| namespaced class names will cause parse error under PHP 5.3.
|
*/

require __DIR__.'/autoload.php';

$app = require_once __DIR__.'/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);
