<?php
/**
 * @Author: printempw
 * @Date:   2016-01-16 23:01:33
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-03-27 14:59:05
 *
 * Blessing Skin Server Installer
 */

$dir = dirname(dirname(__FILE__));
require "$dir/libraries/autoloader.php";
$step = isset($_GET['step']) ? $_GET['step'] : 1;
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta name="viewport" content="width=device-width" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="robots" content="noindex,nofollow" />
<title>Blessing Skin Server 安装程序</title>
<link rel="stylesheet" type="text/css" href="../assets/css/install.style.css">
</head>
<body class="container">
<p id="logo"><a href="https://github.com/printempw/blessing-skin-server" tabindex="-1">Blessing Skin Server</a></p>
<?php

// use error control to hide shitty connect warnings
error_reporting(0);
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWD, DB_NAME, DB_PORT);
error_reporting(E_ALL ^ E_NOTICE);

if ($conn->connect_error): ?>
<h1>MySQL 连接错误</h1>
<p>无法连接至 MySQL 服务器，确定你在 config.php 填写的数据库信息正确吗？</p>
<p>详细信息：<?php echo $conn->connect_error; ?></p>
<?php exit; endif;
$conn->query("SET names 'utf8'");

if (Database\Database::checkTableExist($conn)): ?>
<h1>已安装过</h1>
<p>Blessing Skin Server 看起来已经安装妥当。如果想重新安装，请删除数据库中的旧数据表，或者换一个数据表前缀。</p>
<p class="step"><a href="../index.php" class="button button-large">返回首页</a></p>
<?php exit; endif;

/*
 * Stepped installation
 */
switch ($step) {

// Step 1
case 1: ?>
<h1>填写信息</h1>
<p>您需要填写一些基本信息。无需担心填错，这些信息以后可以再次修改。</p>
<form id="setup" method="post" action="install.php?step=2" novalidate="novalidate">
    <table class="form-table">
        <tr>
            <th scope="row"><label for="username">管理员用户名</label></th>
            <td>
                <input name="username" type="text" id="username" size="25" value="" />
                <p>用户名只能含有数字、字母、下划线。这是唯一的管理员账号。</p>
            </td>
        </tr>
        <tr class="form-field form-required">
            <th scope="row"><label for="password">密码</label></th>
            <td>
                <input type="password" name="password" id="password" class="regular-text" autocomplete="off" />
                <p>
                    <span class="description important">
                        <b>重要：</b>您将需要此密码来登录管理皮肤站，请将其保存在安全的位置。
                    </span>
                </p>
            </td>
        </tr>
        <tr class="form-field form-required">
            <th scope="row"><label for="password2">重复密码(必填)</label></th>
            <td>
                <input type="password" name="password2" id="password2" autocomplete="off" />
                <p>
                    <span class="description important"></span>
                </p>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="username">站点名称</label></th>
            <td>
                <input name="sitename" type="text" id="sitename" size="25" value="" />
                <p>
                    <span class="description important">
                        将会显示在首页以及标题栏，最好用纯英文（字体原因）
                    </span>
                </p>
            </td>
        </tr>
    </table>
<?php if (isset($_GET['msg'])) echo "<div class='alert alert-warning' role='alert'>".$_GET['msg']."</div>"; ?>
<p class="step"><input type="submit" name="Submit" id="submit" class="button button-large" value="开始安装"  /></p>
</form>
<?php break;

// Step 2
case 2:
// check post
if (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['password2'])) {
    if ($_POST['password'] != $_POST['password2']) {
        header('Location: install.php?step=2&msg=确认密码不一致。'); exit;
    }
    $username = $_POST['username'];
    $password = $_POST['password'];
    $sitename = isset($_POST['sitename']) ? $_POST['sitename'] : "Blessing Skin Server";
    if (User::checkValidUname($username)) {
        if (strlen($password) > 16 || strlen($password) < 5) {
            header('Location: install.php?step=2&msg=无效的密码。密码长度应该大于 6 并小于 15。');
            exit;
        } else if (Utils::convertString($password) != $password) {
            header('Location: install.php?step=2&msg=无效的密码。密码中包含了奇怪的字符。'); exit;
        }
    } else {
        header('Location: install.php?step=2&msg=无效的用户名。用户名只能包含数字，字母以及下划线。'); exit;
    }
} else {
    header('Location: install.php?step=2&msg=表单信息不完整。'); exit;
}

$table_users   = DB_PREFIX."users";
$table_options = DB_PREFIX."options";

$sql1  =  "CREATE TABLE IF NOT EXISTS `$table_users` (
              `uid` int(20) NOT NULL AUTO_INCREMENT,
              `username` varchar(50) NOT NULL,
              `password` varchar(255) NOT NULL,
              `ip` varchar(32) NOT NULL,
              `preference` varchar(10) NOT NULL,
              `hash_steve` varchar(64),
              `hash_alex` varchar(64),
              `hash_cape` varchar(64),
              `last_modified` datetime,
              PRIMARY KEY (`uid`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$sql2  =  "CREATE TABLE IF NOT EXISTS `$table_options` (
              `option_id` int(20) unsigned NOT NULL AUTO_INCREMENT,
              `option_name` varchar(50) NOT NULL,
              `option_value` longtext,
              PRIMARY KEY (`option_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

// import options
$sql3  =  "INSERT INTO `$table_options` (`option_id`, `option_name`, `option_value`) VALUES
            (1,  'site_url',           ''),
            (2,  'site_name',          '$sitename'),
            (3,  'site_description',   'Minecraft 皮肤站'),
            (4,  'current_version',    '2.3.4'),
            (5,  'user_can_register',  '1'),
            (6,  'regs_per_ip',        '2'),
            (7,  'api_type',           '0'),
            (8,  'announcement',       '这是默认的公告~'),
            (9,  'data_adapter',       ''),
            (10, 'data_table_name',    'authme for example'),
            (11, 'data_column_uname',  'username'),
            (12, 'data_column_passwd', 'password'),
            (13, 'data_column_ip',     'ip'),
            (14, 'color_scheme',       'skin-blue'),
            (15, 'home_pic_url',       './assets/images/bg.jpg');";

if (!$conn->query($sql1) || !$conn->query($sql2) || !$conn->query($sql3)) { ?>
    <h1>数据表创建失败</h1>
    <p>照理来说不应该的，请带上错误信息联系作者：</p>
    <p><?php echo $conn->error; ?></p>
    <?php exit;
}

// Insert user
$conn->query("INSERT INTO `$table_users` (`uid`, `username`, `password`, `ip`, `preference`) VALUES
    (1, '".$username."', '".md5($_POST['password'])."', '127.0.0.1', 'default')");

if (!is_dir("../textures/")) {
    if (!mkdir("../textures/")): ?>
    <h1>文件夹创建失败</h1>
    <p>textures 文件夹创建失败。确定你拥有该目录的写权限吗？</p>
    <?php endif;
} ?>

<h1>成功！</h1>
<p>Blessing Skin Server 安装完成。您是否还沉浸在愉悦的安装过程中？很遗憾，一切皆已完成！ :)</p>
<table class="form-table install-success">
    <tr>
        <th>用户名</th>
        <td><?php echo $username; ?></td>
    </tr>
    <tr>
        <th>密码</th>
        <td><p><em><?php echo $password; ?></em></p></td>
    </tr>
</table>
<p class="step"><a href="../index.php" class="button button-large">首页</a></p>
<?php break;
} ?>
</body>
</html>
