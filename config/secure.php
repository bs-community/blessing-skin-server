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
    'cipher' => env('PWD_METHOD', 'SALTED2MD5'),
    'salt'   => env('SALT', ''),

    /*
    |--------------------------------------------------------------------------
    | SSL Certificates
    |--------------------------------------------------------------------------
    |
    | Describes the SSL certificate verification behavior of all Guzzle requests.
    | By default, we use the CA bundle provided by Mozilla.
    |
    | See: http://docs.guzzlephp.org/en/stable/request-options.html#verify
    |
    */
    'certificates' => env('SSL_CERT', storage_path('patches/ca-bundle.crt')),
    'user_agent' => env('USER_AGENT', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.140 Safari/537.36'),
];
