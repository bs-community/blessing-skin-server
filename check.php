<?php
header('Access-Control-Allow-Origin：*');
session_start();
$action = $_GET['action'];
require "./connect.php";
global $arr;

if ($action == "login") {
    // SQL injection protection will be done in connect.php
    $uname = $_POST['uname'];
    $passwd = md5(stripslashes(trim($_POST['passwd']))); // Use md5 to encrypt password
    $arr = checkPasswd($uname, $passwd);
    //$arr['msg'] = $uname;
} elseif ($action == "token") {
    $uname = $_COOKIE['uname'];
    $token = $_POST['token'];
    $arr = checkToken($uname, $token);
} elseif ($action == "register") {
    $uname = $_POST['uname'];
    $passwd = md5(stripslashes(trim($_POST['passwd'])));

    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    $arr = register($uname, $passwd, $ip);
}


echo json_encode($arr);
?>
