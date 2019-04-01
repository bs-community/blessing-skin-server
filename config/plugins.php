<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Plugins Directory
    |--------------------------------------------------------------------------
    |
    | The absolute path for loading plugins.
    | Defaults to `base_path()."/plugins"`.
    |
    */
    'directory' => env('PLUGINS_DIR'),

    /*
    |--------------------------------------------------------------------------
    | Plugins Assets URL
    |--------------------------------------------------------------------------
    |
    | The URL to access plugin's assets (CSS, JavaScript etc.).
    | Defaults to `http://site_url/plugins`.
    |
    */
    'url' => env('PLUGINS_URL'),

    /*
    |--------------------------------------------------------------------------
    | Plugins Market Source
    |--------------------------------------------------------------------------
    |
    | Specify where to get plugins' metadata for plugin market.
    |
    */
    'registry' => env(
        'PLUGINS_REGISTRY',
        'https://dev.azure.com/blessing-skin/0dc12c60-882a-46a2-90c6-9450490193a2/_apis/'.
        'git/repositories/d5283b63-dfb0-497e-ad17-2860a547596f/Items?path=%2Fregistry.json'
    ),
];
