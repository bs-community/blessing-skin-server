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
    'github' => [
        'name'        => 'GitHub',
        'update_url'  => 'https://work.prinzeugen.net/update.json',
        'description' => '从 <a href="https://prinzeugen.net/">作者主机</a> 上取更新信息，GitHub Releases 上取更新包。国内主机可能会奇慢无比，请注意。'
    ],
    'nyavm' => [
        'name'        => 'NyaVM',
        'update_url'  => 'http://anycast.cdn.nyavm.net/',
        'description' => '啦啦啦'
    ],
    'little_service' => [
        'name'        => 'LittleService-COS',
        'update_url'  => 'http://cos.littleservice.cn/bs/update.json',
        'description' => '由 <a href="http://littleqiu.net/">Little_Qiu</a> 及其 <a href="http://studio.littleservice.cn/">团队</a> 维护的非官方更新源，国内主机使用可能会获得一定的加速 Buff。不建议海外主机的用户使用。'
    ],
    'local' => [
        'name'        => 'LocalHost',
        'update_url'  => 'http://127.0.0.1/test/update.json',
        'description' => '开发用，请勿选择（炸了别怪我）'
    ]
);
