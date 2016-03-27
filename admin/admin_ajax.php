<?php
/**
 * @Author: printempw
 * @Date:   2016-02-04 13:53:55
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-03-27 11:29:16
 */
require "../libraries/session.inc.php";

// Check token, won't allow non-admin user to access
if (!$user->is_admin) header('Location: ../index.php?msg=看起来你并不是管理员');

/*
 * No protection here,
 * I don't think you wanna fuck yourself :(
 */
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $user = new User(isset($_GET['uname']) ? $_GET['uname'] : '');

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
            throw new E('你没有选择任何文件哦', 1);
        }
    } else if ($action == "change") {
        if (User::checkValidPwd($_POST['passwd'])) {
            $user->changePasswd($_POST['passwd']);
            $json['errno'] = 0;
            $json['msg'] = "成功更改了 ".$_GET['uname']." 的密码。";
        } // Will raise exception if password invalid
    } else if ($action == "deleteAccount") {
        $user->unRegister();
        $json['errno'] = 0;
        $json['msg'] = "成功删除了该用户。";
    } else if ($action == "deleteTexture") {
        for ($i = 1; $i <= 3; $i++) {
            switch($i) {
                case 1: $type = "steve"; break;
                case 2: $type = "alex"; break;
                case 3: $type = "cape"; break;
            }
            if ($_POST[$type] == "true" && $user->getTexture($type) != "") {
                Utils::remove("./textures/".$user->getTexture($type));
                $user->db->update($user->uname, 'hash_'.$type, '');
            }
        }
        $json['errno'] = 0;
        $json['msg'] = "成功地删除了该用户的所选材质。";
    } else if ($action == "model") {
        if (isset($_POST['model']) && $_POST['model'] == 'slim' || $_POST['model'] == 'default') {
            $user->setPreference($_POST['model']);
            $json['errno'] = 0;
            $json['msg'] = "成功地将用户 ".$_GET['uname']." 的优先皮肤模型更改为 ".$_POST['model']." 。";
        } else {
            throw new E('非法参数。', 1);
        }
    } else if ($action == "color") {
        if (isset($_POST['color_scheme'])) {
            $color_scheme = str_replace('_', '-', $_POST['color_scheme']);
            Option::set('color_scheme', $color_scheme);
            $json['errno'] = 0;
            $json['msg'] = "修改配色成功。";
        } else {
            throw new E('非法参数。', 1);
        }
    } else {
        throw new E('非法参数。', 1);
    }
}

echo json_encode($json);
