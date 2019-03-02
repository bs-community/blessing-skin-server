<?php

/**
 * Entrance of Blessing Skin Server.
 *
 * @author   printempw <h@prinzeugen.net>
 */
@ini_set('display_errors', 'on');

require __DIR__.'/../bootstrap/autoload.php';

if (! isset($GLOBALS['env_checked'])) {
    require __DIR__.'/../bootstrap/chkenv.php';
}

// Process the request
require __DIR__.'/../bootstrap/kernel.php';
