<?php
/**
 * @Author: printempw
 * @Date:   2016-01-16 23:01:33
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-03-18 14:50:43
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
Database::checkConfig();

if (isset($_POST['uname'])) {
    $uname = $_POST['uname'];
    if (User::checkValidUname($uname)) {
        $user = new User($_POST['uname']);
    } else {
        Utils::raise(1, '无效的用户名。用户名只能包含数字，字母以及下划线。');
    }
} else {
    Utils::raise('1', '空用户名。');
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
            $json['msg'] = "用户不存在哦";
        } else {
            if ($user->checkPasswd($_POST['passwd'])) {
                $json['errno'] = 0;
                $json['msg'] = '登录成功，欢迎回来~';
                $json['token'] = $user->getToken();
                $_SESSION['token'] = $user->getToken();
            } else {
                $json['errno'] = 1;
                $json['msg'] = "用户名或密码不对哦";
            }
        }
    }
} else if ($action == "register") {
    if (checkPost('register')) {
        if (!$user->is_registered) {
            if (Config::get('user_can_register') == 1) {
                if (User::checkValidPwd($_POST['passwd'])) {
                    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                        $ip = $_SERVER['HTTP_CLIENT_IP'];
                    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                    } else {
                        $ip = $_SERVER['REMOTE_ADDR'];
                    }
                    // If amount of registered accounts of IP is more than allowed mounts,
                    // then reject the registration.
                    if ($user->db->getNumRows('ip', $ip) < Config::get('regs_per_ip')) {
                        // use once md5 to encrypt password
                        if ($user->register($_POST['passwd'], $ip)) {
                            $json['errno'] = 0;
                            $json['msg'] = "注册成功~";
                        } else {
                            $json['errno'] = 1;
                            $json['msg'] = "出现了奇怪的错误。。请联系作者 :(";
                        }
                    } else {
                        $json['errno'] = 1;
                        $json['msg'] = "你最多只能注册 ".Config::get('regs_per_ip')." 个账户哦";
                    }
                }
            } else {
                $json['errno'] = 1;
                $json['msg'] = "残念。。本皮肤站已经关闭注册咯 QAQ";
            }
        } else {
            $json['errno'] = 1;
            $json['msg'] = "这个用户名已经被人注册辣，换一个吧";
        }
    }
}

function checkPost() {
    global $json;
    if (!isset($_POST['passwd'])) {
        $json['errno'] = 1;
        $json['msg'] = "空密码。";
        return false;
    }
    return true;
}

/**
 * Handle request from user/index.php
 */
if ($action == "upload") {
    if (Utils::getValue('token', $_SESSION) == $user->getToken()) {
        if (checkFile()) {
            if ($file = Utils::getValue('skin_file', $_FILES)) {
                $model = (isset($_GET['model']) && $_GET['model'] == "steve") ? "steve" : "alex";
                if ($user->setTexture($model, $file)) {
                    $json['skin']['errno'] = 0;
                    $json['skin']['msg'] = "皮肤上传成功！";
                } else {
                    $json['skin']['errno'] = 1;
                    $json['skin']['msg'] = "出现了奇怪的错误。。请联系作者 :(";
                }
            }
            if ($file = Utils::getValue('cape_file', $_FILES)) {
                if ($user->setTexture('cape', $file)) {
                    $json['cape']['errno'] = 0;
                    $json['cape']['msg'] = "披风上传成功！";
                } else {
                    $json['cape']['errno'] = 1;
                    $json['cape']['msg'] = "出现了奇怪的错误。。请联系作者 :(";
                }
            }
        }
    } else {
        $json['errno'] = 1;
        $json['msg'] = "无效的 token，请先登录。";
    }
} else if ($action == "model") {
    if (Utils::getValue('token', $_SESSION) == $user->getToken()) {
        $new_model = ($user->getPreference() == "default") ? "slim" : "default";
        $user->setPreference($new_model);
        $json['errno'] = 0;
        $json['msg'] = "优先模型已经更改为 ".$user->getPreference()."。";
    } else {
        $json['errno'] = 1;
        $json['msg'] = "无效的 token，请先登录。";
    }
}

function checkFile() {
    global $json;

    if (!(Utils::getValue('skin_file', $_FILES) || Utils::getValue('cape_file', $_FILES))) {
        $json['errno'] = 1;
        $json['msg'] = "什么文件都没有诶？";
        return false;
    }

    /**
     * Check for skin_file
     */
    if (isset($_FILES['skin_file']) && ($_FILES['skin_file']['type'] == "image/png" ||
                                            $_FILES['skin_file']['type'] == "image/x-png"))
    {
        // if error occured while uploading file
        if (isset($_FILES['skin_file']) && $_FILES['skin_file']["error"] > 0) {
            $json['errno'] = 1;
            $json['msg'] = $_FILES['skin_file']["error"];
            return false;
        }
    } else {
        if (Utils::getValue('skin_file', $_FILES)) {
            $json['errno'] = 1;
            $json['msg'] = '错误的皮肤文件类型。';
            return false;
        } else {
            $json['skin']['errno'] = 0;
            $json['skin']['msg'] = '什么文件都没有诶？';
        }
    }

    /**
     * Check for cape_file
     */
    if (isset($_FILES['cape_file']) && ($_FILES['cape_file']['type'] == "image/png" ||
                                            $_FILES['cape_file']['type'] == "image/x-png"))
    {
        // if error occured while uploading file
        if (isset($_FILES['cape_file']) && $_FILES['cape_file']["error"] > 0) {
            $json['errno'] = 1;
            $json['msg'] = $_FILES['cape_file']["error"];
            return false;
        }
    } else {
        if (Utils::getValue('cape_file', $_FILES)) {
            $json['errno'] = 1;
            $json['msg'] = '错误的披风文件类型。';
            return false;
        } else {
            $json['cape']['errno'] = 0;
            $json['cape']['msg'] = '什么文件都没有诶？';
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
                $json['msg'] = "密码更改成功。请重新登录。";
            } else {
                $json['errno'] = 1;
                $json['msg'] = "原密码不对哦？";
            }
        } else {
            $json['errno'] = 1;
            $json['msg'] = "新密码呢？";
        }
    }
} else if ($action == "delete") {
    if (isset($_SESSION['token']) && $_SESSION['token'] == $user->getToken()) {
        if (checkPost()) {
            if ($user->checkPasswd($_POST['passwd'])) {
                session_destroy();
                $user->unRegister();
                $json['errno'] = 0;
                $json['msg'] = "账号已经成功删除，再见~";
            } else {
                $json['errno'] = 1;
                $json['msg'] = "错误的密码。";
            }
        }
    } else {
        $json['errno'] = 1;
        $json['msg'] = "无效的 token，请先登录。";
    }
} else if ($action == "reset") {
    if (isset($_SESSION['token']) && $_SESSION['token'] == $user->getToken()) {
        if (checkPost()) {
            if ($user->checkPasswd($_POST['passwd'])) {
                $user->reset();
                $json['errno'] = 0;
                $json['msg'] = "重置成功。";
            } else {
                $json['errno'] = 1;
                $json['msg'] = "错误的密码。";
            }
        }
    } else {
        $json['errno'] = 1;
        $json['msg'] = "无效的 token，请先登录。";
    }
} else if ($action == "logout") {
    if (Utils::getValue('token', $_SESSION)) {
        session_destroy();
        $json['errno'] = 0;
        $json['msg'] = '成功登出 | ﾟ ∀ﾟ)ノ';
    } else {
        $json['errno'] = 1;
        $json['msg'] = '并没有任何有效的 session。';
    }
}

if (!$action) {
    $json['errno'] = 1;
    $json['msg'] = "无效的参数。不要乱 POST 玩哦。";
}

echo json_encode($json);
