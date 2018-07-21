<?php

(function () {
    function die_with_utf8_encoding($error)
    {
        header('Content-Type: text/html; charset=UTF-8');
        exit($error);
    }

    if (version_compare(PHP_VERSION, '7.1.3', '<')) {
        die_with_utf8_encoding(
            '[Error] Blessing Skin requires PHP version >= 7.1.3, you are now using '.PHP_VERSION.'<br>'.
            '[错误] 你的 PHP 版本过低（'.PHP_VERSION.'），Blessing Skin 要求至少为 7.1.3'
        );
    }

    $requirements = [
        'extensions' => [
            'pdo',
            'openssl',
            'gd',
            'mbstring',
            'tokenizer',
            'ctype',
            'xml',
            'json',
            'fileinfo'
        ],
        'write_permission' => [
            'bootstrap/cache',
            'storage',
            'plugins'
        ]
    ];

    foreach ($requirements['extensions'] as $extension) {
        if (! extension_loaded($extension)) {
            die_with_utf8_encoding(
                "[Error] You have not installed the $extension extension <br>".
                "[错误] 你尚未安装 $extension 扩展！安装方法请自行搜索。"
            );
        }
    }

    foreach ($requirements['write_permission'] as $dir) {
        $realPath = realpath(__DIR__."/../$dir");

        if (! file_exists($realPath)) {
            die_with_utf8_encoding(
                "[Error] The directory < $dir > does not exist <br>".
                "[错误] 目录 < $dir > 不存在，请在程序根目录下手动创建。"
            );
        }

        if (! is_writable($realPath)) {
            die_with_utf8_encoding(
                "[Error] The program lacks write permission to directory < $dir > <br>".
                "[错误] 程序缺少对 < $dir > 目录的写权限，请手动授权。"
            );
        }
    }

    $autoload = file_get_contents('vendor/autoload.php');
    $lines = explode("\n", $autoload);
    $lines[1] = '$GLOBALS["env_checked"] = true;';
    file_put_contents('vendor/autoload.php', implode("\n", $lines));
})();
