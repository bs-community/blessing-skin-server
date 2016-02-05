<?php
/**
 * @Author: printempw
 * @Date:   2016-01-16 23:01:33
 * @Last Modified by:   prpr
 * @Last Modified time: 2016-02-05 15:35:31
 *
 * - login, register, logout
 * - upload, change, delete
 *
 * All ajax requests will be handled here
 */

session_start();
header('Access-Control-Allow-Origin: *');
header('Content-type: application/json');

$dir = dirname(__FILE__);
require "$dir/includes/autoload.inc.php";
database::checkConfig();

if (isset($_POST['uname'])) {
    $uname = $_POST['uname'];
    if (user::checkValidUname($uname)) {
        $user = new user($_POST['uname']);
    } else {
        utils::raise(1, 'Invalid username. Only letters, numbers and _ is allowed.');
    }
} else {
    utils::raise('1', 'Empty username.');
}
$action = isset($_GET['action']) ? $_GET['action'] : null;
$json = null;

/**
 * Handle requests from index.php
 */
if ($action == "login") {
    if (checkPost()) {
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
    if (checkPost('register')) {
        if (!$user->is_registered) {
            if (user::checkValidPwd($_POST['passwd'])) {
                if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                    $ip = $_SERVER['HTTP_CLIENT_IP'];
                } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                } else {
                    $ip = $_SERVER['REMOTE_ADDR'];
                }
                // If amount of registered accounts of IP is more than allowed mounts,
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
            }
        } else {
            $json['errno'] = 1;
            $json['msg'] = "User already registered.";
        }
    }
}

function checkPost() {
    global $json;
    if (!isset($_POST['passwd'])) {
        $json['errno'] = 1;
        $json['msg'] = "Empty password!";
        return false;
    }
    return true;
}

/**
 * Handle request from user/index.php
 */
if ($action == "upload") {
    if (utils::getValue('token', $_SESSION) == $user->getToken()) {
        if (checkFile()) {
            if ($file = utils::getValue('skin_file', $_FILES)) {
                $model = (isset($_GET['model']) && $_GET['model'] == "steve") ? "steve" : "alex";
                if ($user->setTexture($model, $file)) {
                    $json['skin']['errno'] = 0;
                    $json['skin']['msg'] = "Skin uploaded successfully.";
                } else {
                    $json['skin']['errno'] = 1;
                    $json['skin']['msg'] = "Uncaught error.";
                }
            }
            if ($file = utils::getValue('cape_file', $_FILES)) {
                if ($user->setTexture('cape', $file)) {
                    $json['cape']['errno'] = 0;
                    $json['cape']['msg'] = "Cape uploaded successfully.";
                } else {
                    $json['cape']['errno'] = 1;
                    $json['cape']['msg'] = "Uncaught error.";
                }
            }
        }
    } else {
        $json['errno'] = 1;
        $json['msg'] = "Invalid token.";
    }
} else if ($action == "model") {
    if (utils::getValue('token', $_SESSION) == $user->getToken()) {
        $new_model = ($user->getPreference() == "default") ? "slim" : "default";
        $user->setPreference($new_model);
        $json['errno'] = 0;
        $json['msg'] = "Preferred model successfully changed to ".$user->getPreference().".";
    } else {
        $json['errno'] = 1;
        $json['msg'] = "Invalid token.";
    }
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
            $json['skin']['errno'] = 0;
            $json['skin']['msg'] = 'No skin file selected.';
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
            $json['cape']['errno'] = 0;
            $json['cape']['msg'] = 'No cape file selected.';
        }
    }

    return true;
}

/**
 * Handle requests from user/profile.php
 */
if ($action == "change") {
    if (checkPost()) {
        if (isset($_POST['new_passwd'])) {
            if ($user->checkPasswd($_POST['passwd'])) {
                $user->changePasswd($_POST['new_passwd']);
                $json['errno'] = 0;
                $json['msg'] = "Password updated successfully.";
            } else {
                $json['errno'] = 1;
                $json['msg'] = "Incorrect usename or password.";
            }
        } else {
            $json['errno'] = 1;
            $json['msg'] = "New password required.";
        }
    }
} else if ($action == "delete") {
    if (isset($_SESSION['token']) && $_SESSION['token'] == $user->getToken()) {
        if (checkPost()) {
            if ($user->checkPasswd($_POST['passwd'])) {
                session_destroy();
                $user->unRegister();
                $json['errno'] = 0;
                $json['msg'] = "Account successfully deleted.";
            } else {
                $json['errno'] = 1;
                $json['msg'] = "Incorrect password.";
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

if (!$action) {
    $json['errno'] = 1;
    $json['msg'] = "Invalid parameters.";
}

echo json_encode($json);
