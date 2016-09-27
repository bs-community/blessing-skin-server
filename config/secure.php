<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuration about security
    |--------------------------------------------------------------------------
    |
    | Load them from env to config, preventing cache problems
    |
    */
    'cipher' => menv('PWD_METHOD', 'SALTED2MD5'),
    'salt'   => menv('APP_KEY', '')
];
