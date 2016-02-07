<?php
/**
 * @Author: prpr
 * @Date:   2016-02-04 13:53:55
 * @Last Modified by:   prpr
 * @Last Modified time: 2016-02-06 23:29:34
 */
require "../includes/session.inc.php";

// Check token, won't allow non-admin user to access
if (!$user->is_admin) header('Location: ../index.php?msg=看起来你并不是管理员');

/*
 * No protection here,
 * I don't think you wanna fuck yourself :(
 */
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $user = new User($_GET['uname']);

    if ($action == "upload") {
        $type = isset($_GET['type']) ? $_GET['type'] : "skin";
        $file = isset($_FILES['file']) ? $_FILES['file'] : null;
        if (!is_null($file)) {
            if ($user->setTexture($type, $file)) {
                $json['errno'] = 0;
                $json['msg'] = "皮肤上传成功。";
            } else {
                $json['errno'] = 1;
                $json['msg'] = "出现了奇怪的错误。。请联系作者";
            }
        } else {
            Utils::raise(1, '你没有选择任何文件哦');
        }
    } else if ($action == "change") {
        if (user::checkValidPwd($_POST['passwd'])) {
            $user->changePasswd($_POST['passwd']);
            $json['errno'] = 0;
            $json['msg'] = "成功更改了 ".$_GET['uname']." 的密码。";
        } // Will raise exception if password invalid
    } else if ($action == "delete") {
        $user->unRegister();
        $json['errno'] = 0;
        $json['msg'] = "成功删除了该用户。";
    } else if ($action == "model") {
        if (isset($_POST['model']) && $_POST['model'] == 'slim' || $_POST['model'] == 'default') {
            $user->setPreference($_POST['model']);
            $json['errno'] = 0;
            $json['msg'] = "成功地将用户 ".$_GET['uname']." 的优先皮肤模型更改为 ".$_POST['model']." 。";
        } else {
            Utils::raise(1, '非法参数。');
        }
    } else {
        Utils::raise(1, '非法参数。');
    }
}

echo json_encode($json);
