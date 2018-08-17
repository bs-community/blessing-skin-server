<?php

$msg = [];

try {
    Artisan::call('migrate', ['--force' => true]);
    $msg[] = '【数据库】升级成功！现在你可以正常使用 v3.5.0 内置的用户邮箱验证功能了';
} catch (Exception $e) {
    $msg[] = '【数据库】更新数据表失败，错误信息：'.$e->getMessage();
    $msg[] = '【数据库】这并不影响 v3.5.0 的基本功能，你可以参考 <a href="https://github.com/printempw/blessing-skin-server/wiki/%E6%89%8B%E5%8A%A8%E5%AE%89%E8%A3%85-Blessing-Skin#-%E5%8D%87%E7%BA%A7%E8%87%B3-bs-v350" target="_blank">这篇文章</a> 手动升级你的数据库';
}

$plugins_enabled = (array) json_decode(option('plugins_enabled'), true);

if (in_array('data-integration', $plugins_enabled)) {
    $plugins_enabled = '["data-integration"]';
    $msg[] = '【数据对接】原有的数据对接插件已经不再维护，并且有可能在 v3.5.0 上出现奇怪的问题';
    $msg[] = '【数据对接】请参考 <a href="https://github.com/printempw/blessing-skin-server/wiki/%E5%A6%82%E4%BD%95%E4%BD%BF%E7%94%A8%E6%95%B0%E6%8D%AE%E5%AF%B9%E6%8E%A5" target="_blank">这篇文章</a> 升级你的数据对接插件';
} else {
    $plugins_enabled = '';
}

$msg[] = '【插件系统】升级程序已经自动禁用了所有已安装的插件，因为这些插件的版本可能过旧';
$msg[] = '【插件系统】请在后台的「插件市场」页面升级你的所有插件，确保其为最新版后再启用它们';
$msg[] = '【插件系统】在 v3.5.0 上强行启用旧版的插件可能造成无法预知的问题！';

option(['plugins_enabled' => $plugins_enabled]);
option(['return_204_when_notfound' => option('return_404_when_notfound')]);
option(['version' => config('app.version')]);

$msg[] = '【升级成功】升级完成后请【务必】清空你的浏览器缓存，否则可能会出现奇怪的问题';
$msg[] = '【升级成功】使用愉快！<a href="https://github.com/printempw/blessing-skin-server/wiki/CHANGELOG" target="_blank">查看完整更新日志</a>';

return $msg;
