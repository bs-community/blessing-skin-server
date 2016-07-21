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
    1 => ['title' => '仪表盘',   'link' => '/user',         'icon' => 'fa-dashboard'],
    2 => ['title' => '我的衣柜', 'link' => '/user/closet',  'icon' => 'fa-star'],
    3 => ['title' => '角色管理', 'link' => '/user/player',  'icon' => 'fa-users'],
    4 => ['title' => '配置生成', 'link' => '/user/config',  'icon' => 'fa-book'],
    5 => ['title' => '个人资料', 'link' => '/user/profile', 'icon' => 'fa-user']
);

$menu['admin'] = array(
    1 => ['title' => '仪表盘',   'link' => '/admin',                'icon' => 'fa-dashboard'],
    2 => ['title' => '用户管理', 'link' => '/admin/manage/user',    'icon' => 'fa-users'],
    3 => ['title' => '角色管理', 'link' => '/admin/manage/player',  'icon' => 'fa-users'],
    4 => ['title' => '材质管理', 'link' => '/admin/manage/texture', 'icon' => 'fa-users'],
    5 => ['title' => '个性化',   'link' => '/admin/customize',      'icon' => 'fa-paint-brush'],
    6 => ['title' => '站点配置', 'link' => '/admin/options',        'icon' => 'fa-cog'],
    7 => ['title' => '检查更新', 'link' => '/admin/update',         'icon' => 'fa-arrow-up']
);

return $menu;
