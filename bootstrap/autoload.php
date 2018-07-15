<?php

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Register The Composer Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader
| for our application. We just need to utilize it! We'll require it
| into the script here so that we do not have to worry about the
| loading of any our classes "manually". Feels great to relax.
|
*/

if (file_exists($autoload = __DIR__.'/../vendor/autoload.php')) {
    require $autoload;
} else {
    header('Content-Type: text/html; charset=UTF-8');
    exit(
        "[Error] No vendor folder found. Have you installed the dependencies with composer? <br>".
        "[错误] 根目录下未发现 vendor 文件夹，请使用 composer 安装依赖库。详情请阅读 http://t.cn/REyMUqA"
    );
}
