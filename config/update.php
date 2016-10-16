<?php
/*
|--------------------------------------------------------------------------
| Update Sources
|--------------------------------------------------------------------------
|
| Urls to get update information
|
*/

return array(
    'nyavm' => [
        'name'        => 'LizCat',
        'update_url'  => 'http://cdn.prinzeugen.net/update.json',
        'description' => '感谢 <a href="https://www.nyavm.com/">NyaVM</a> 提供的 Anycast CDN，国内外主机都可获得不错的速度。'
    ],
    'little_service' => [
        'name'        => 'LittleService-COS',
        'update_url'  => 'http://cos.littleservice.cn/bs/update.json',
        'description' => '由 <a href="http://littleqiu.net/">Little_Qiu</a> 及其 <a href="http://studio.littleservice.cn/">团队</a> 维护的非官方更新源，国内主机使用可能会获得一定的加速 Buff。不建议海外主机的用户使用。'
    ],
    'local' => [
        'name'        => 'LocalHost',
        'update_url'  => 'http://127.0.0.1/test/update.json',
        'description' => '本地调试用，请勿选择（炸了别怪我）'
    ]
);
