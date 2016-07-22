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
    1 => ['title' => '仪表盘',   'link' => '/user',            'icon' => 'fa-dashboard'],
    2 => ['title' => '我的衣柜', 'link' => '/user/closet',     'icon' => 'fa-star'],
    3 => ['title' => '角色管理', 'link' => '/user/player',     'icon' => 'fa-users'],
    4 => ['title' => '配置生成', 'link' => '/user/config',     'icon' => 'fa-book'],
    5 => ['title' => '个人资料', 'link' => '/user/profile',    'icon' => 'fa-user']
);

$menu['admin'] = array(
    1 => ['title' => '仪表盘',   'link' => '/admin',           'icon' => 'fa-dashboard'],
    2 => ['title' => '用户管理', 'link' => '/admin/users',      'icon' => 'fa-users'],
    3 => ['title' => '角色管理', 'link' => '/admin/players',    'icon' => 'fa-gamepad'],
    4 => ['title' => '个性化',   'link' => '/admin/customize', 'icon' => 'fa-paint-brush'],
    5 => ['title' => '站点配置', 'link' => '/admin/options',   'icon' => 'fa-cog']
);

return $menu;
