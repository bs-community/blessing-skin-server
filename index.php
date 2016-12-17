<?php

/**
 * Entrance of Blessing Skin Server
 *
 * @package  Blessing Skin Server
 * @author   printempw <h@prinzeugen.net>
 */

require __DIR__.'/bootstrap/autoload.php';

// check the runtime environment
runtime_check([
    'php' => '5.5.9',
    'extensions' => ['pdo_mysql', 'openssl', 'gd']
]);

// handle the request
require __DIR__.'/bootstrap/handler.php';
