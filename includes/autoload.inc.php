<?php
/**
 * @Author: prpr
 * @Date:   2016-02-02 21:17:59
 * @Last Modified by:   prpr
 * @Last Modified time: 2016-02-04 18:34:34
 */

function __autoload($classname) {
    global $dir;
    $filename = "$dir/includes/".$classname.".class.php";
    include_once($filename);
}
require "$dir/config.php";
