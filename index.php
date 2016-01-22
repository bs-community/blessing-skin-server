<?php
/**
 * @Author: printempw
 * @Date:   2016-01-17 13:55:20
 * @Last Modified by:   prpr
 * @Last Modified time: 2016-01-22 14:42:56
 */

$dir = dirname(__FILE__);

function __autoload($classname) {
    global $dir;
    $filename = "$dir/includes/". $classname .".class.php";
    include_once($filename);
}

if ($_GET['action'] == "get") {
    if ($_GET['type'] && $_GET['uname']) {
        $user = new user($_GET['uname']);
        if ($_GET['type'] == "skin") {
            header('Content-Type: image/png');
            echo $user->getBinaryTexture('skin');
        } else if ($_GET['type'] == "cape") {
            header('Content-Type: image/png');
            echo $user->getBinaryTexture('cape');
        } else {
            header('Content-type: application/json');
            echo $user->getJsonProfile();
        }
    } else {
        utils::raise(1, 'Illegal parameters.');
    }
} else {
    include("$dir/templates/index.inc.php");
}
