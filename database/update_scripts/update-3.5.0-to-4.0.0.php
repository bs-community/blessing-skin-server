<?php

$msg = [];

try {
    Artisan::call('migrate', ['--force' => true]);
    $msg[] = '【数据库】升级成功！';
} catch (Exception $e) {
    $msg[] = '【数据库】更新数据表失败，错误信息：'.$e->getMessage();
}

Artisan::call('view:clear');

option(['version' => '4.0.0']);
