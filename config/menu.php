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
    ['title' => 'general.my-reports',     'link' => 'user/reports', 'icon' => 'fa-flag'],
    ['title' => 'general.profile',        'link' => 'user/profile', 'icon' => 'fa-user'],
    [
        'title' => 'general.developer',
        'icon' => 'fa-code-branch',
        'children' => [
            ['title' => 'general.oauth-manage', 'link' => 'user/oauth/manage', 'icon' => 'fa-feather-alt'],
        ],
    ],
];

$menu['admin'] = [
    ['title' => 'general.dashboard',      'link' => 'admin',                'icon' => 'fa-tachometer-alt'],
    ['title' => 'general.user-manage',    'link' => 'admin/users',          'icon' => 'fa-users'],
    ['title' => 'general.player-manage',  'link' => 'admin/players',        'icon' => 'fa-gamepad'],
    ['title' => 'general.report-manage',  'link' => 'admin/reports',        'icon' => 'fa-flag'],
    ['title' => 'general.customize',      'link' => 'admin/customize',      'icon' => 'fa-paint-brush'],
    ['title' => 'general.score-options',  'link' => 'admin/score',          'icon' => 'fa-credit-card'],
    ['title' => 'general.options',        'link' => 'admin/options',        'icon' => 'fa-cog'],
    ['title' => 'general.res-options',    'link' => 'admin/resource',       'icon' => 'fa-atom'],
    ['title' => 'general.status',         'link' => 'admin/status',         'icon' => 'fa-battery-three-quarters'],
    ['title' => 'general.plugin-manage',  'link' => 'admin/plugins/manage', 'icon' => 'fa-plug'],
    ['title' => 'general.plugin-market',  'link' => 'admin/plugins/market', 'icon' => 'fa-shopping-bag'],
    ['title' => 'general.plugin-configs', 'id'   => 'plugin-configs',       'icon' => 'fa-cogs', 'children' => []],
    ['title' => 'general.check-update',   'link' => 'admin/update',         'icon' => 'fa-arrow-up'],
];

$menu['explore'] = [
    ['title' => 'general.skinlib',         'link' => 'skinlib',              'icon' => 'fa-archive'],
];

return $menu;
