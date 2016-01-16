<?php
/**
 * @Author: printempw
 * @Date:   2016-01-16 23:01:33
 * @Last Modified by:   prpr
 * @Last Modified time: 2016-01-17 00:01:57
 *
 * All ajax requests will be handled here
 */

header('Access-Control-Allow-Origin：*');
session_start();

function __autoload($classname) {
    $filename = "./includes/". $classname .".class.php";
    include_once($filename);
}

$user = new user($_POST['uname']);
$action = $_GET['action'];
$json = null;

function checkPost() {
    global $json;
    if (!$_POST['uname']) {
        $json['errno'] = 1;
        $json['msg'] = 'Empty username!';
        return false;
    } else if (!$_POST['passwd']) {
        $json['errno'] = 1;
        $json['msg'] = "Empty password!";
        return false;
    }
    return true;
}

if ($action == "login") {
    if (checkPost()) {
        if (!$user -> is_registered) {
            $json['errno'] = 1;
            $json['msg'] = "Non-existent user.";
        } else {
            if ($user -> checkPasswd($_POST['passwd'])) {
                $json['errno'] = 0;
                $json['msg'] = 'Logging in succeed!';
                $json['token'] = $user -> getToken();
            } else {
                $json['errno'] = 1;
                $json['msg'] = "Incorrect usename or password.";
            }
        }
    }
} elseif ($action == "register") {

} elseif ($action == "register") {

}

echo json_encode($json);
