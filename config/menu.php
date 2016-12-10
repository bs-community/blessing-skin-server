<?php
/*
|--------------------------------------------------------------------------
| Sidebar Menus
|--------------------------------------------------------------------------
|
| Register your custom sidebar menu here.
|
*/

$menu['user'] = array(
    ['title' => 'general.dashboard',       'link' => 'user',         'icon' => 'fa-dashboard'],
    ['title' => 'general.my-closet',       'link' => 'user/closet',  'icon' => 'fa-star'],
    ['title' => 'general.player-manage',   'link' => 'user/player',  'icon' => 'fa-users'],
    ['title' => 'general.profile',         'link' => 'user/profile', 'icon' => 'fa-user'],
);

$menu['admin'] = array(
    ['title' => 'general.dashboard',     'link' => 'admin',            'icon' => 'fa-dashboard'],
    ['title' => 'general.user-manage',   'link' => 'admin/users',      'icon' => 'fa-users'],
    ['title' => 'general.player-manage', 'link' => 'admin/players',    'icon' => 'fa-gamepad'],
    ['title' => 'general.plugin-manage', 'icon' => 'fa-plug', 'children' => [
        ['title' => 'general.plugin-market',    'link' => 'admin/plugins/market', 'icon' => 'fa-shopping-bag'],
        ['title' => 'general.plugin-installed', 'link' => 'admin/plugins/manage', 'icon' => 'fa-download'],
    ]],
    ['title' => 'general.customize',     'link' => 'admin/customize',  'icon' => 'fa-paint-brush'],
    ['title' => 'general.score-options', 'link' => 'admin/score',      'icon' => 'fa-credit-card'],
    ['title' => 'general.options',       'link' => 'admin/options',    'icon' => 'fa-cog'],
    ['title' => 'general.check-update',  'link' => 'admin/update',     'icon' => 'fa-arrow-up'],
);

return $menu;
