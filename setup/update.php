<?php
/**
 * @Author: printempw
 * @Date:   2016-04-03 19:20:47
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-04-03 21:56:13
 */

session_start();
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
<title>Blessing Skin Server 升级程序</title>
<link rel="stylesheet" type="text/css" href="../assets/css/install.style.css">
</head>
<body class="container">
<p id="logo"><a href="https://github.com/printempw/blessing-skin-server" tabindex="-1">Blessing Skin Server</a></p>
<?php

$updater = new Updater(Option::get('current_version'));

if (!$updater->newVersionAvailable()): ?>
<h1>并没有可用的更新</h1>
<p>当前版本：v<?php echo $updater->current_version; ?></p>
<p class="step"><a href="../index.php" class="button button-large">返回首页</a></p>
<?php exit; endif;

if (!isset($_SESSION['downloaded_version'])): ?>
<h1>非法访问</h1>
<p>下载完更新后再来吧</p>
<p class="step"><a href="../admin/update.php" class="button button-large">检查更新</a></p>
<?php exit; endif;

/*
 * Stepped installation
 */
switch ($step) {
// Step 1
case 1: ?>
<h1>还差一小步</h1>
<p>我们需要先升级下数据库。点击下一步以继续。</p>
<p class="step"><a href="?step=2" class="button button-large">下一步</a></p>
<?php break;

// Step 2
case 2: ?>
<h1>升级数据库</h1>
<?php
$resource = opendir(dirname(__FILE__)."/update_scripts/");

while($filename = @readdir($resource)) {
    if ($filename != "." && $filename != "..") {
        preg_match('/update-(.*)-to-(.*).php/', $filename, $matches);
        if (!isset($matches[2])) continue;
        include dirname(__FILE__)."/update_scripts/".$filename;
    }
}
closedir($resource);

echo "<p>数据库升级成功。欢迎使用 Blessing Skin Server v".Option::get('current_version')."！</p>"
?>
<p class="step"><a href="../index.php" class="button button-large">进入首页</a></p>
<?php break; ?>

</body>
</html>
<?php }
