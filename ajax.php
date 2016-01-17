<?php
/**
 * @Author: printempw
 * @Date:   2016-01-16 23:01:33
 * @Last Modified by:   prpr
 * @Last Modified time: 2016-01-17 10:51:05
 *
 * All ajax requests will be handled here
 */

header('Access-Control-Allow-Origin: *');
session_start();

function __autoload($classname) {
    $filename = "./includes/". $classname .".class.php";
    include_once($filename);
}

$user = new user($_POST['uname']);
$action = $_GET['action'];
$json = null;

function checkInput($type = "login") {
    global $json;
    // generally check username
    if (!$_POST['uname']) {
        $json['errno'] = 1;
        $json['msg'] = 'Empty username!';
        return false;
    }
    if ($type == "login" || $type == "register") {
        if (!$_POST['passwd']) {
            $json['errno'] = 1;
            $json['msg'] = "Empty password!";
            return false;
        }
        return true;
    } else if ($type == "upload") {
        if (!($_FILES['skin_file'] || $_FILES['cape_file'])) {
            $json['errno'] = 1;
            $json['msg'] = "No input file selected.";
            return false;
        }
        return true;
    }
}

if ($action == "login") {
    if (checkInput($action)) {
        if (!$user -> is_registered) {
            $json['errno'] = 1;
            $json['msg'] = "Non-existent user.";
        } else {
            if ($user -> checkPasswd($_POST['passwd'])) {
                $json['errno'] = 0;
                $json['msg'] = 'Logging in succeed!';
                $json['token'] = $user -> getToken();
                $_SESSION['token'] = $user -> getToken();
            } else {
                $json['errno'] = 1;
                $json['msg'] = "Incorrect usename or password.";
            }
        }
    }
} elseif ($action == "register") {
    if (checkInput($action)) {
        if (!$user -> is_registered) {
            if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else {
                $ip = $_SERVER['REMOTE_ADDR'];
            }

            if (!utils::select('ip', $ip)) {
                // use once md5 to encrypt password
                if ($user -> register(md5($_POST['passwd']), $ip)) {
                    $json['errno'] = 0;
                    $json['msg'] = "Registered successfully.";
                } else {
                    $json['errno'] = 1;
                    $json['msg'] = "Uncaught error.";
                }
            } else {
                $json['errno'] = 1;
                $json['msg'] = "It seems that you have already register a account with this IP address.";
            }

        } else {
            $json['errno'] = 1;
            $json['msg'] = "User already existed.";
        }
    }
} elseif ($action == "upload") {
    if ($_SESSION['token'] == $user -> getToken()) {
        if (checkInput($action)) {
            if ($file = $_FILES['skin_file']) {
                if ($user -> setTexture('skin', $file)) {
                    $json[0]['errno'] = 0;
                    $json[0]['msg'] = "Skin uploaded successfully.";
                } else {
                    $json[0]['errno'] = 1;
                    $json[0]['msg'] = "Uncaught error.";
                }
            }
            if ($file = $_FILES['cape_file']) {
                if ($user -> setTexture('cape', $file)) {
                    $json[1]['errno'] = 0;
                    $json[1]['msg'] = "Cape uploaded successfully.";
                } else {
                    $json[1]['errno'] = 1;
                    $json[1]['msg'] = "Uncaught error.";
                }
            }
        }
    } else {
        $json['errno'] = 1;
        $json['msg'] = "Invalid token.";
    }
}

echo json_encode($json);
