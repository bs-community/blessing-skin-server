<?php
/**
 * @Author: printempw
 * @Date:   2016-03-27 13:30:00
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-03-27 17:01:20
 */

// Sanity check
if (false): ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta name="viewport" content="width=device-width" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="robots" content="noindex,nofollow" />
<title>出现错误 - Blessing Skin Server 安装程序</title>
<link rel="stylesheet" type="text/css" href="../assets/css/install.style.css">
</head>
<body class="container">
<p id="logo"><a href="https://github.com/printempw/blessing-skin-server" tabindex="-1">Blessing Skin Server</a></p>
<h1>错误：PHP 未运行</h1>
<p>Blessing Skin Server 基于 PHP 开发，需要 PHP 运行环境。如果你看到这段话就说明主机的 PHP 未运行。</p>
<p>你问 PHP 是什么？为什么不问问神奇海螺呢？</p>
</body>
</html>
<?php endif;

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

<h1>欢迎</h1>
<p>欢迎使用 Blessing Skin Server V2！</p>
<p>成功连接至 MySQL 服务器 <?php echo DB_USER."@".DB_HOST; ?>，点击下一步以开始安装。</p>
<p class="step"><a href="?step=2" class="button button-large">下一步</a></p>
<?php break;

// Step 2
case 2: ?>
<h1>功能检查</h1>
<p>我们需要做一些检查来确保你可以正常使用 Blessing Skin Server。</p>

<?php
$fails = 0;

function checkFunc($func_name) {
    global $fails;
    if (function_exists($func_name)) {
        return '<span class="result passed">可用</span>';
    } else {
        $fails++;
        return '<span class="result failed">不支持</span>';
    }
}

function checkClass($classname) {
    global $fails;
    if (class_exists($classname)) {
        return '<span class="result passed">可用</span>';
    } else {
        $fails++;
        return '<span class="result failed">不支持</span>';
    }
}

function checkRewrite() {
    global $fails;
    $protocol = "http://";

    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 1) {  // Apache
        $protocol = "https://";
    } elseif ($_SERVER['HTTPS'] === 'on') { // IIS
        $protocol = "https://";
    } elseif ($_SERVER['SERVER_PORT'] == 443){ // for other servers
        $protocol = "https://";
    }

    $uri = $protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    $base_url = explode('setup', $uri)[0];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $base_url."check_for_rewrite_rules.json");
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch,CURLOPT_NOBODY,true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($ch);
    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200) {
        return '<span class="result passed">可用</span>';
    } else {
        $fails++;
        return '<span class="result failed">不支持</span>';
    }
    curl_close($ch);
}

?>

<div class="test">
    <span class="test-name">PHP 版本</span>
    <?php if (strnatcasecmp(phpversion(), '5.4') >= 0): ?>
    <span class="result passed"><?php echo phpversion(); ?></span>
    <?php else: ?>
    <span class="result failed"><?php echo phpversion(); ?></span>
    <?php $fails++; endif; ?>
    <div class="info">由于使用了一些新特性，最低需要 PHP 5.4。</div>
</div>

<div class="test">
    <span class="test-name">MySQLi</span>
    <?php echo checkClass('mysqli'); ?>
    <div class="info">数据库操作。</div>
</div>

<div class="test">
    <span class="test-name">重写规则</span>
    <?php echo checkRewrite(); ?>
    <div class="info">伪静态，用于支持传统皮肤获取链接。</div>
</div>

<div class="test">
    <span class="test-name">JSON Encode</span>
    <?php echo checkFunc('json_encode'); ?>
    <div class="info">编码 JSON，用于支持 JSON API。</div>
</div>

<div class="test">
    <span class="test-name">写入权限</span>
    <?php if (is_writable(BASE_DIR)): ?>
    <span class="result passed">可写</span>
    <?php else: ?>
    <span class="result failed">不可写</span>
    <?php $fails++; endif; ?>
    <div class="info">目前不支持 SAE 等不可写应用引擎。</div>
</div>

<div class="test">
    <span class="test-name">ZipArchive</span>
    <?php echo checkClass('ZipArchive'); ?>
    <div class="info">解压缩，用于自动升级。</div>
</div>

<div class="test">
    <span class="test-name">cURL</span>
    <?php echo checkFunc('curl_exec'); ?>
    <div class="info">抓取网页，用于自动升级。</div>
</div>

<?php
if ($fails == 0) {
    echo '<p class="step"><a href="install.php" class="button button-large">下一步</a></p>';
} else {
    echo '<p class="step"><a disabled="disabled" class="button button-large">下一步</a></p>';
}
?>

</body>
</html>
<?php }
