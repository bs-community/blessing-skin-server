<?php
/**
 * @Author: prpr
 * @Date:   2016-02-04 13:53:55
 * @Last Modified by:   prpr
 * @Last Modified time: 2016-02-04 17:14:06
 */
session_start();
$dir = dirname(dirname(__FILE__));
require "$dir/includes/autoload.inc.php";
require "$dir/config.php";

if(isset($_COOKIE['uname']) && isset($_COOKIE['token'])) {
    $_SESSION['uname'] = $_COOKIE['uname'];
    $_SESSION['token'] = $_COOKIE['token'];
}

/**
 * Check token, won't allow non-admin user to access
 */
if (isset($_SESSION['uname'])) {
    $admin = new user($_SESSION['uname']);
    if ($_SESSION['token'] != $admin->getToken()) {
        header('Location: ../index.php?msg=Invalid token. Please login.');
    } else if (!$admin->is_admin) {
        header('Location: ../index.php?msg=Looks like that you are not administrator :(');
    }
} else {
    header('Location: ../index.php?msg=Illegal access. Please login.');
}

/*
 * No protection here,
 * I don't think you wanna fuck yourself :(
 */
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $user = new user($_GET['uname']);

    if ($action == "upload") {
        $type = isset($_GET['type']) ? $_GET['type'] : "skin";
        $file = isset($_FILES['file']) ? $_FILES['file'] : null;
        if (!is_null($file)) {
            if ($user->setTexture($type, $file)) {
                $json['errno'] = 0;
                $json['msg'] = "Skin uploaded successfully.";
            } else {
                $json['errno'] = 1;
                $json['msg'] = "Uncaught error.";
            }
        } else {
            utils::raise(1, 'No input file selected');
        }
    } else if ($action == "change") {
        if (user::checkValidPwd($_POST['passwd'])) {
            $user->changePasswd($_POST['passwd']);
            $json['errno'] = 0;
            $json['msg'] = "Password of ".$_GET['uname']." changed successfully.";
        } // Will raise exception if password invalid
    } else if ($action == "delete") {
        $user->unRegister();
        $json['errno'] = 0;
        $json['msg'] = "Account successfully deleted.";
    }
}

echo json_encode($json);
