<?php
/**
 * @Author: printempw
 * @Date:   2016-02-06 23:18:49
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-05-12 21:54:47
 */
session_start();
$dir = dirname(dirname(__FILE__));
require "$dir/libraries/autoloader.php";
Database\Database::checkConfig();

if(isset($_COOKIE['uname']) && isset($_COOKIE['token'])) {
    $_SESSION['uname'] = $_COOKIE['uname'];
    $_SESSION['token'] = $_COOKIE['token'];
}

if (isset($_SESSION['uname'])) {
    $user = new User($_SESSION['uname']);
    if ($_SESSION['token'] != $user->getToken()) {
        Utils::redirect('../index.php', '无效的 token，请重新登录。');
    }
} else {
    Utils::redirect('../index.php', '非法访问，请先登录。');
}
