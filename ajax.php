<?php
/**
 * @Author: printempw
 * @Date:   2016-01-16 23:01:33
 * @Last Modified by:   prpr
 * @Last Modified time: 2016-02-02 23:28:03
 *
 * All ajax requests will be handled here
 */

header('Access-Control-Allow-Origin: *');
session_start();
$dir = dirname(__FILE__);
require "$dir/includes/autoload.inc.php";
require "$dir/config.php";

database::checkConfig();

if (isset($_POST['uname'])) {
    $user = new user($_POST['uname']);
} else {
    utils::raise('1', 'Empty username.');
}
$action = isset($_GET['action']) ? $_GET['action'] : "login";
$json = null;

if ($action == "login") {
    if (checkInput()) {
        if (!$user->is_registered) {
            $json['errno'] = 1;
            $json['msg'] = "Non-existent user.";
        } else {
            if ($user->checkPasswd($_POST['passwd'])) {
                $json['errno'] = 0;
                $json['msg'] = 'Logging in succeed!';
                $json['token'] = $user->getToken();
                $_SESSION['token'] = $user->getToken();
            } else {
                $json['errno'] = 1;
                $json['msg'] = "Incorrect usename or password.";
            }
        }
    }
} else if ($action == "register") {
    if (checkInput()) {
        if (!$user->is_registered) {
            if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
            // If amout of registered accounts of IP is more than allowed mounts,
            // then reject the registration.
            if ($user->db->getNumRows('ip', $ip) < REGS_PER_IP) {
                // use once md5 to encrypt password
                if ($user->register(md5($_POST['passwd']), $ip)) {
                    $json['errno'] = 0;
                    $json['msg'] = "Registered successfully.";
                } else {
                    $json['errno'] = 1;
                    $json['msg'] = "Uncaught error.";
                }
            } else {
                $json['errno'] = 1;
                $json['msg'] = "You can't create more than ".REGS_PER_IP." accounts with this IP.";
            }

        } else {
            $json['errno'] = 1;
            $json['msg'] = "User already existed.";
        }
    }
} else if ($action == "upload") {
    if ($_SESSION['token'] == $user->getToken()) {
        if (checkFile()) {
            if ($file = utils::getValue('skin_file', $_FILES)) {
                if ($user->setTexture('skin', $file)) {
                    $json[0]['errno'] = 0;
                    $json[0]['msg'] = "Skin uploaded successfully.";
                } else {
                    $json[0]['errno'] = 1;
                    $json[0]['msg'] = "Uncaught error.";
                }
            }
            if ($file = utils::getValue('cape_file', $_FILES)) {
                if ($user->setTexture('cape', $file)) {
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
} else if ($action == "logout") {
    if (utils::getValue('token', $_SESSION)) {
        session_destroy();
        $json['errno'] = 0;
        $json['msg'] = 'Session destroyed.';
    } else {
        $json['errno'] = 1;
        $json['msg'] = 'No available session.';
    }
}

function checkInput() {
    global $json;
    if (!$_POST['uname']) {
        $json['errno'] = 1;
        $json['msg'] = 'Empty username!';
        return false;
    }
    if (!$_POST['passwd']) {
        $json['errno'] = 1;
        $json['msg'] = "Empty password!";
        return false;
    }
    return true;
}

function checkFile() {
    global $json;

    if (!(utils::getValue('skin_file', $_FILES) || utils::getValue('cape_file', $_FILES))) {
        $json['errno'] = 1;
        $json['msg'] = "No input file selected.";
        return false;
    }
    /**
     * Check for skin_file
     */
    if ((utils::getValue('skin_file', $_FILES)["type"] == "image/png") || (utils::getValue('skin_file', $_FILES)["type"] == "image/x-png")) {
        // if error occured while uploading file
        if (utils::getValue('skin_file', $_FILES)["error"] > 0) {
            $json['errno'] = 1;
            $json['msg'] = utils::getValue('skin_file', $_FILES)["error"];
            return false;
        }
    } else {
        if (utils::getValue('skin_file', $_FILES)) {
            $json['errno'] = 1;
            $json['msg'] = 'Skin file type error.';
            return false;
        } else {
            $json[0]['errno'] = 0;
            $json[0]['msg'] = 'No skin file selected.';
        }
    }

    /**
     * Check for cape_file
     */
    if ((utils::getValue('cape_file', $_FILES)["type"] == "image/png") || (utils::getValue('cape_file', $_FILES)["type"] == "image/x-png")) {
        // if error occured while uploading file
        if (utils::getValue('cape_file', $_FILES)["error"] > 0) {
            $json['errno'] = 1;
            $json['msg'] = utils::getValue('cape_file', $_FILES)["error"];
            return false;
        }
    } else {
        if (utils::getValue('cape_file', $_FILES)) {
            $json['errno'] = 1;
            $json['msg'] = 'Cape file type error.';
            return false;
        } else {
            $json[1]['errno'] = 0;
            $json[1]['msg'] = 'No cape file selected.';
        }
    }

    return true;
}

echo json_encode($json);
