<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | Load them from env to config, preventing cache problems
    |
    */
    'cipher' => env('PWD_METHOD', 'BCRYPT'),
    'salt' => env('SALT', ''),
];
