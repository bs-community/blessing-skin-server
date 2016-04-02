<?php
/**
 * @Author: printempw
 * @Date:   2016-02-02 21:17:59
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-04-02 19:19:48
 */

function __autoload($classname) {
    global $dir;
    // echo $classname.'<br />';
    $include_dir = $dir.DIRECTORY_SEPARATOR."libraries".DIRECTORY_SEPARATOR;
    $filename = $include_dir.str_replace('\\', DIRECTORY_SEPARATOR, $classname) . '.class.php';
    if (!file_exists($filename))
        exit("Undefined class `$classname` @ `$filename`");
    require_once($filename);
}
if (!file_exists($dir.'/config.php'))
    throw new E('未找到 `config.php`，请确认配置文件是否存在。', -1, true);
require "$dir/config.php";
