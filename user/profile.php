<?php
/**
 * @Author: prpr
 * @Date:   2016-02-03 16:12:45
 * @Last Modified by:   prpr
 * @Last Modified time: 2016-02-06 23:29:32
 */
require "../includes/session.inc.php";
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>个人设置 - <?php echo SITE_TITLE; ?></title>
    <link rel="shortcut icon" href="../assets/images/favicon.ico">
    <link rel="stylesheet" href="../libs/pure/pure-min.css">
    <link rel="stylesheet" href="../libs/pure/grids-responsive-min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/profile.style.css">
    <link rel="stylesheet" href="../libs/ply/ply.css">
</head>
<body>
<div class="header">
    <div class="home-menu pure-menu pure-menu-horizontal pure-menu-fixed">
        <a class="pure-menu-heading" href="../index.php"><?php echo SITE_TITLE; ?></a>
        <a href="javascript:;" title="Movements"><span class="glyphicon glyphicon-pause"></span></a>
        <a href="javascript:;" title="Running"><span class="glyphicon glyphicon-forward"></span></a>
        <a href="javascript:;" title="Rotation"><span class="glyphicon glyphicon-repeat"></span></a>
        <ul class="pure-menu-list">
            <li class="pure-menu-item">
                <a class="pure-menu-link" href="index.php">皮肤上传</a>
            </li>
            <li class="pure-menu-item">
                <span class="pure-menu-link">欢迎，<?php echo $_SESSION['uname']; ?>！</span>|<span class="pure-menu-link" id="logout">登出？</span>
             </li>
        </ul>
        <div class="home-menu-blur">
            <div class="home-menu-wrp">
                <div class="home-menu-bg"></div>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="pure-g">
        <div class="pure-u-1 pure-u-md-1-2">
            <div class="panel panel-default">
                <div class="panel-heading">更改密码</div>
                <div class="panel-body">
                    <div class="pure-form pure-form-stacked">
                        <input id="passwd" type="password" placeholder="旧的密码">
                        <input id="new-passwd" type="password" placeholder="新密码">
                        <input id="confirm-pwd" type="password" placeholder="确认密码">
                        <button id="change" class="pure-button pure-button-primary">修改密码</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="pure-u-1 pure-u-md-1-2">
            <div class="panel panel-danger">
                <div class="panel-heading">删除账号</div>
                <div class="panel-body">
                    <p>确定要删除你在 <?php echo SITE_TITLE; ?> 上的账号吗？</p>
                    <p>此操作不可恢复！我们不提供任何备份，或者神奇的撤销按钮。</p>
                    <p>我们警告过你了，确定要这样做吗？</p>
                    <button id="delete" class="pure-button pure-button-error">删除我的账户</button>
                </div>
            </div>
        </div>
    </div>
    <div class="pure-g">
        <div class="pure-u-1 pure-u-md-1-2">
            <div class="panel panel-default">
                <div class="panel-heading">如何使用？</div>
                <div class="panel-body">
                    <p>Check it here: <a href="https://github.com/printempw/blessing-skin-server/blob/master/README.md">printempw/blessing-skin-server</a></p>
                </div>
            </div>
        </div>
        <?php if ($user->is_admin) { ?>
        <div class="pure-u-1 pure-u-md-1-2">
            <div class="panel panel-default">
                <div class="panel-heading">欢迎，尊敬的管理员</div>
                <div class="panel-body">
                    <p>在这里管理你的皮肤站： <a href="../admin/">仪表盘</a></p>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>
</div>

</body>
<script type="text/javascript" src="../libs/jquery/jquery-2.1.1.min.js"></script>
<script type="text/javascript" src="../libs/ply/ply.min.js"></script>
<script type="text/javascript" src="../libs/cookie.js"></script>
<script type="text/javascript" src="../assets/js/utils.js"></script>
<script type="text/javascript" src="../assets/js/profile.utils.js"></script>
</html>
