<?php
/**
 * @Author: prpr
 * @Date:   2016-02-03 14:39:50
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-03-06 14:40:17
 */
require "../includes/session.inc.php";
if (!$user->is_admin) header('Location: ../index.php?msg=看起来你并不是管理员');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>仪表盘 - <?php echo SITE_TITLE; ?></title>
    <link rel="shortcut icon" href="../assets/images/favicon.ico">
    <link rel="stylesheet" href="../libs/pure/pure-min.css">
    <link rel="stylesheet" href="../libs/pure/grids-responsive-min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/profile.style.css">
    <link rel="stylesheet" href="../assets/css/admin.style.css">
    <link rel="stylesheet" href="../libs/ply/ply.css">
</head>

<body>
<div class="header">
    <div class="home-menu pure-menu pure-menu-horizontal pure-menu-fixed">
        <a class="pure-menu-heading" href="../index.php"><?php echo SITE_TITLE; ?></a>
        <ul class="pure-menu-list">
            <li class="pure-menu-item">
                <a class="pure-menu-link" href="../user/profile.php">个人设置</a>
            </li>
            <?php include "../includes/welcome.inc.php"; ?>
        </ul>
        <div class="home-menu-blur">
            <div class="home-menu-wrp">
                <div class="home-menu-bg"></div>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="panel panel-default overview">
        <div class="panel-heading">概览</div>
        <div class="panel-body">
            <?php
            $page_now = isset($_GET['page']) ? $_GET['page'] : 1;
            $db = new Database();
            ?>
            <p>注册用户：<?php echo $db->getRecordNum();?></p>
            <p>上传材质总数：<?php echo count(scandir("../textures/"))-2;?></p>
            <p>占用空间大小：<?php echo floor(Utils::getDirSize("../textures/")/1024)."KB";?></p>
        </div>
    </div>
</div>

</body>
<script type="text/javascript" src="../libs/jquery/jquery-2.1.1.min.js"></script>
<script type="text/javascript" src="../libs/ply/ply.min.js"></script>
<script type="text/javascript" src="../libs/cookie.js"></script>
<script type="text/javascript" src="../assets/js/utils.js"></script>
<script type="text/javascript" src="../assets/js/admin.utils.js"></script>
</html>
</body>
</html>
