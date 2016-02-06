<?php
/**
 * @Author: prpr
 * @Date:   2016-02-06 23:18:49
 * @Last Modified by:   prpr
 * @Last Modified time: 2016-02-06 23:27:48
 */
session_start();
$dir = dirname(dirname(__FILE__));
require "$dir/includes/autoload.inc.php";

if(isset($_COOKIE['uname']) && isset($_COOKIE['token'])) {
    $_SESSION['uname'] = $_COOKIE['uname'];
    $_SESSION['token'] = $_COOKIE['token'];
}

if (isset($_SESSION['uname'])) {
    $user = new User($_SESSION['uname']);
    if ($_SESSION['token'] != $user->getToken()) {
        header('Location: ../index.php?msg=无效的 token，请重新登录。');
    }
} else {
    header('Location: ../index.php?msg=非法访问，请先登录。');
}
