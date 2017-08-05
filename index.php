<?php

/**
 * Entrance of Blessing Skin Server
 *
 * @package  Blessing Skin Server
 * @author   printempw <h@prinzeugen.net>
 */

@ini_set('display_errors', 'on');

// check PHP version
if (version_compare(PHP_VERSION, '5.5.9', '<')) {
    exit(
        '[Error] Blessing Skin Server needs PHP version >= 5.5.9, you are now using '.PHP_VERSION.'<br>'.
        '[错误] 你的 PHP 版本过低（'.PHP_VERSION.'），Blessing Skin Server 要求至少为 5.5.9'
    );
}

require __DIR__.'/bootstrap/autoload.php';

// check the runtime environment
runtime_check(array(
    'extensions' => array('pdo_mysql', 'openssl', 'gd', 'mbstring', 'tokenizer'),
    'write_permission' => array('storage', 'resources', 'plugins')
));

// handle the request
require __DIR__.'/bootstrap/kernel.php';
