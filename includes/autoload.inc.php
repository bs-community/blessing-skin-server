<?php
/**
 * @Author: prpr
 * @Date:   2016-02-02 21:17:59
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-03-12 18:04:46
 */

function __autoload($classname) {
    global $dir;
    $filename = "$dir/includes/".$classname.".class.php";
    include_once($filename);
}
if (!file_exists($dir.'/config.php'))
    Utils::showErrorPage(-1, '未找到 `config.php`，请确认配置文件是否存在。');
require "$dir/config.php";
