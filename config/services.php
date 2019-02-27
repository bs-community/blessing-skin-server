<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, Mandrill, and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'guzzle' => [
            'verify' => config('secure.certificates')
        ],
    ],

    'mandrill' => [
        'secret' => env('MANDRILL_SECRET'),
        'guzzle' => [
            'verify' => config('secure.certificates')
        ],
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => env('SES_REGION'),
        'guzzle' => [
            'verify' => config('secure.certificates')
        ],
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
        'guzzle' => [
            'verify' => config('secure.certificates')
        ],
    ],

];
