<?php

/**
 * Entrance of Blessing Skin Server
 *
 * @package  Blessing Skin Server
 * @author   printempw <h@prinzeugen.net>
 */

// runtime check
if (version_compare(PHP_VERSION, '5.5.9', '<')) {
    exit('[Error] Blessing Skin Server needs PHP version >= 5.5.9, you are now using '.PHP_VERSION);
}

if (!class_exists('PDO')) {
    exit('[Error] You have not installed the PDO extension');
}

if (!function_exists('openssl_encrypt')) {
    exit('[Error] You have not installed the OpenSSL extension');
}

require __DIR__.'/bootstrap/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);
