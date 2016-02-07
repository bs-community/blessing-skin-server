<?php
/**
 * @Author: prpr
 * @Date:   2016-02-03 14:39:50
 * @Last Modified by:   prpr
 * @Last Modified time: 2016-02-06 23:29:33
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
    <table class="pure-table pure-table-horizontal">
        <thead>
            <tr>
                <th>#</th>
                <th>用户名</th>
                <th>预览材质</th>
                <th>更改材质</th>
                <th>操作</th>
            </tr>
        </thead>

        <tbody>
            <?php
            $db = new Database();
            $result = $db->query("SELECT * FROM users");
            while ($row = $result->fetch_array()) { ?>
            <tr>
                <td><?php echo $row['uid']; ?></td>
                <td><?php echo $row['username']; ?></td>
                <td>
                    <?php echo '<img id="'.$row['username'].'_skin" width="64" '.(($row['hash_steve'] == "") ? '' : 'src="../skin/'.$row['username'].'-steve.png"').'/>'; ?>
                    <?php echo '<img id="'.$row['username'].'_skin" width="64" '.(($row['hash_alex'] == "") ? '' : 'src="../skin/'.$row['username'].'-alex.png"').'/>'; ?>
                    <?php echo '<img id="'.$row['username'].'_cape" width="64" '.(($row['hash_cape'] == "") ? '' : 'src="../cape/'.$row['username'].'.png"').'/>'; ?>
                </td>
                <td>
                    <a href="javascript:uploadSkin('<?php echo $row['username']; ?>');" class="pure-button pure-button-primary">皮肤</a>
                    <a href="javascript:uploadTexture('<?php echo $row['username']; ?>', 'cape');" class="pure-button pure-button-primary">披风</a>
                    <a href="javascript:changeModel('<?php echo $row['username']; ?>');" class="pure-button pure-button-default">优先模型</a>
                    <span>(<?php echo $row['preference']; ?>)</span>
                </td>
                <td>
                    <a href="javascript:changePasswd('<?php echo $row['username'] ?>');" class="pure-button pure-button-default">更改密码</a>
                    <a href="javascript:deleteAccount('<?php echo $row['username'] ?>');" class="pure-button pure-button-error">删除用户</a>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
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
