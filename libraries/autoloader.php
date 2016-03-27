<?php
/**
 * @Author: printempw
 * @Date:   2016-02-02 21:17:59
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-03-27 11:19:58
 */

function __autoload($classname) {
    global $dir;
    // echo $classname.'<br />';
    $include_dir = $dir.DIRECTORY_SEPARATOR."libraries".DIRECTORY_SEPARATOR;
    $filename = $include_dir.str_replace('\\', DIRECTORY_SEPARATOR, $classname) . '.class.php';
    require_once($filename);
}
if (!file_exists($dir.'/config.php'))
    throw new E('未找到 `config.php`，请确认配置文件是否存在。', -1, true);
require "$dir/config.php";
