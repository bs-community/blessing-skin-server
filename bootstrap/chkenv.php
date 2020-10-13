<?php

use Illuminate\Encryption\Encrypter;

ini_set('display_errors', true);

(function () {
    function die_with_utf8_encoding($error)
    {
        header('Content-Type: text/html; charset=UTF-8');
        exit($error);
    }

    if (!file_exists(__DIR__.'/../vendor/autoload.php')) {
        die_with_utf8_encoding(
            '[Error] No vendor folder found. Please use the released built package.<br>'.
            '[错误] 根目录下未发现 vendor 文件夹，请使用正式的已构建好的 release 包。'
        );
    }

    $envPath = __DIR__.'/../.env';
    if (!file_exists($envPath)) {
        copy(__DIR__.'/../.env.example', $envPath);
    }

    $envFile = file_get_contents($envPath);
    if (preg_match('/APP_KEY\s*=\s*\n/', $envFile)) {
        $key = 'base64:'.base64_encode(Encrypter::generateKey('AES-256-CBC'));
        file_put_contents($envPath, preg_replace('/APP_KEY\s*=\s*/', 'APP_KEY='.$key."\n\n", $envFile));
    }

    $requiredFunctions = ['symlink', 'readlink', 'putenv', 'realpath'];
    $disabledFunctions = preg_split('/,\s*/', ini_get('disable_functions'));
    foreach ($requiredFunctions as $fn) {
        if (in_array($fn, $disabledFunctions)) {
            die_with_utf8_encoding(
                '[Error] Please don\'t disable built-in function "'.$fn.'", which is specified in "php.ini" file.<br>'.
                "[错误] 请不要在 php.ini 中禁用 $fn 函数。".
                '<strong>我们不建议使用您使用宝塔等面板软件，因为容易引起兼容性问题。</strong>'
            );
        }
    }

    if (!empty(ini_get('open_basedir'))) {
        die_with_utf8_encoding(
            '[Error] Please disable "open_basedir" option by editing "php.ini" file.<br>'.
            '[错误] 请修改 php.ini 以关闭 "open_basedir" 选项。'.
            '<strong>我们不建议使用您使用宝塔等面板软件，因为容易引起兼容性问题。</strong>'
        );
    }

    $requiredVersion = '7.4.0';
    preg_match('/(\d+\.\d+\.\d+)/', PHP_VERSION, $matches);
    $version = $matches[1];
    if (version_compare($version, $requiredVersion, '<')) {
        die_with_utf8_encoding(
            '[Error] Blessing Skin requires PHP version >= '.$requiredVersion.', you are now using '.$version.'<br>'.
            '[错误] 你的 PHP 版本过低（'.$version.'），Blessing Skin 要求至少为 '.$requiredVersion
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
            'zip',
        ],
        'write_permission' => [
            'bootstrap/cache',
            'storage',
            'plugins',
            'public',
        ],
    ];

    foreach ($requirements['extensions'] as $extension) {
        if (!extension_loaded($extension)) {
            die_with_utf8_encoding(
                "[Error] You have not installed the $extension extension <br>".
                "[错误] 你尚未安装 $extension 扩展！安装方法请自行搜索。"
            );
        }
    }

    foreach ($requirements['write_permission'] as $dir) {
        $realPath = realpath(__DIR__."/../$dir");

        if (!is_writable($realPath)) {
            die_with_utf8_encoding(
                "[Error] The directory '$dir' is not writable. <br>".
                "[错误] 目录 '$dir' 不可写，请检查该目录的权限。"
            );
        }

        if (!is_writable($realPath)) {
            die_with_utf8_encoding(
                "[Error] The program lacks write permission to directory '$dir' <br>".
                "[错误] 程序缺少对 '$dir' 目录的写权限，请手动授权。"
            );
        }
    }
})();
