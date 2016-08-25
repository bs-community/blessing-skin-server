<?php
/*
|--------------------------------------------------------------------------
| Class Aliases
|--------------------------------------------------------------------------
|
| This array of class aliases will be registered when this application
| is started. However, feel free to register as many as you wish as
| the aliases are "lazy" loaded so they don't hinder performance.
|
*/

return [
    'View'      => 'Blessing\View',
    'DB'        => 'Blessing\Facades\DB',
    'Option'    => 'Blessing\Option',
    'Utils'     => 'App\Services\Utils',
    'Validate'  => 'App\Services\Validate',
    'Http'      => 'Blessing\Http',
    'Mail'      => 'Blessing\Mail',
    'Storage'   => 'Blessing\Storage',
    'Minecraft' => 'App\Services\Minecraft',
    'Updater'   => 'App\Services\Updater',
    'Config'    => 'Blessing\Config',
    'Schema'    => 'Blessing\Database\Schema',
    'Boot'      => 'Blessing\Foundation\Boot',
    'Migration' => 'Blessing\Database\Migration',
    'App'       => 'Blessing\Facades\App'
];
