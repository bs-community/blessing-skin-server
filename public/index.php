<?php

@ini_set('display_errors', 'on');

require __DIR__.'/../bootstrap/autoload.php';

if (! file_exists(__DIR__.'/../storage/install.lock')) {
    require __DIR__.'/../bootstrap/chkenv.php';
}

require __DIR__.'/../bootstrap/kernel.php';
