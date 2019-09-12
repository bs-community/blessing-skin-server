<?php

(function () {
    function die_with_utf8_encoding($error)
    {
        header('Content-Type: text/html; charset=UTF-8');
        exit($error);
    }

    preg_match('/(\d+\.\d+\.\d+)/', PHP_VERSION, $matches);
    $version = $matches[1];
    if (version_compare($version, '7.2.0', '<')) {
        die_with_utf8_encoding(
            '[Error] Blessing Skin requires PHP version >= 7.2.0, you are now using '.$version.'<br>'.
            '[错误] 你的 PHP 版本过低（'.$version.'），Blessing Skin 要求至少为 7.2.0'
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
            'fileinfo',
        ],
        'write_permission' => [
            'bootstrap/cache',
            'storage',
            'plugins',
        ],
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

        if (! is_writable($realPath)) {
            die_with_utf8_encoding(
                "[Error] The directory '$dir' is not writable. <br>".
                "[错误] 目录 '$dir' 不可写，请检查该目录的权限。"
            );
        }

        if (! is_writable($realPath)) {
            die_with_utf8_encoding(
                "[Error] The program lacks write permission to directory '$dir' <br>".
                "[错误] 程序缺少对 '$dir' 目录的写权限，请手动授权。"
            );
        }
    }
})();
