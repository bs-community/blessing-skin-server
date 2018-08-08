<?php
/*
|--------------------------------------------------------------------------
| Sidebar Menus
|--------------------------------------------------------------------------
|
| Register your custom sidebar menu here.
|
*/

$menu['user'] = [
    ['title' => 'general.dashboard',      'link' => 'user',         'icon' => 'fa-tachometer-alt'],
    ['title' => 'general.my-closet',      'link' => 'user/closet',  'icon' => 'fa-star'],
    ['title' => 'general.player-manage',  'link' => 'user/player',  'icon' => 'fa-users'],
    ['title' => 'general.profile',        'link' => 'user/profile', 'icon' => 'fa-user'],
];

$menu['admin'] = [
    ['title' => 'general.dashboard',      'link' => 'admin',                'icon' => 'fa-tachometer-alt'],
    ['title' => 'general.user-manage',    'link' => 'admin/users',          'icon' => 'fa-users'],
    ['title' => 'general.player-manage',  'link' => 'admin/players',        'icon' => 'fa-gamepad'],
    ['title' => 'general.customize',      'link' => 'admin/customize',      'icon' => 'fa-paint-brush'],
    ['title' => 'general.score-options',  'link' => 'admin/score',          'icon' => 'fa-credit-card'],
    ['title' => 'general.plugin-market',  'link' => 'admin/plugins/market', 'icon' => 'fa-shopping-bag'],
    ['title' => 'general.plugin-manage',  'link' => 'admin/plugins/manage', 'icon' => 'fa-plug'],
    ['title' => 'general.plugin-configs', 'id'   => 'plugin-configs',       'icon' => 'fa-cogs', 'children' => []],
    ['title' => 'general.options',        'link' => 'admin/options',        'icon' => 'fa-cog'],
    ['title' => 'general.check-update',   'link' => 'admin/update',         'icon' => 'fa-arrow-up'],
];

return $menu;
