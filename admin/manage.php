<?php
/**
 * @Author: printempw
 * @Date:   2016-03-06 14:19:20
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-03-06 15:32:20
 */
require "../includes/session.inc.php";
if (!$user->is_admin) header('Location: ../index.php?msg=看起来你并不是管理员');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户管理 - <?php echo SITE_TITLE; ?></title>
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
                <a class="pure-menu-link" href="index.php">仪表盘</a>
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
            $page_now = isset($_GET['page']) ? $_GET['page'] : 1;
            $db = new Database();
            $result = $db->query("SELECT * FROM users ORDER BY `uid` LIMIT ".(string)(($page_now-1)*30).", 30");
            $page_total = $db->getRecordNum()/30;
            while ($row = $result->fetch_array()) { ?>
            <tr>
                <td><?php echo $row['uid']; ?></td>
                <td><?php echo $row['username']; ?></td>
                <td>
                    <img width="64" <?php if ($row['hash_steve']): ?>src="../skin/<?php echo $row['username']; ?>-steve.png"<?php endif; ?> />
                    <img width="64" <?php if ($row['hash_alex']): ?>src="../skin/<?php echo $row['username']; ?>-alex.png"<?php endif; ?> />
                    <img width="64" <?php if ($row['hash_cape']): ?>src="../cape/<?php echo $row['username']; ?>.png"<?php endif; ?> />
                </td>
                <td>
                    <a href="javascript:uploadSkin('<?php echo $row['username']; ?>');" class="pure-button pure-button-primary">皮肤</a>
                    <a href="javascript:uploadTexture('<?php echo $row['username']; ?>', 'cape');" class="pure-button pure-button-primary">披风</a>
                    <a href="javascript:changeModel('<?php echo $row['username']; ?>');" class="pure-button pure-button-default">优先模型</a>
                    <span>(<?php echo $row['preference']; ?>)</span>
                </td>
                <td>
                    <a href="javascript:deleteTexture('<?php echo $row['username'] ?>');" class="pure-button pure-button-warning">删除材质</a>
                    <a href="javascript:changePasswd('<?php echo $row['username'] ?>');" class="pure-button pure-button-default">更改密码</a>
                    <a href="javascript:deleteAccount('<?php echo $row['username'] ?>');" class="pure-button pure-button-error">删除用户</a>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
    <ul class="pagination">
        <?php if ($page_now == 1): ?>
        <li class="disabled">
            <a href="#" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a>
        </li>
        <?php else: ?>
        <li>
            <a href="manage.php?page=<?php echo $page_now-1; ?>" aria-label="Previous">
                <span aria-hidden="true">&laquo;</span>
            </a>
        </li>
        <?php endif;

        for ($i = 1; $i <= $page_total; $i++) {
            if ($i == $page_now) {
                echo '<li class="active"><a href="#">'.(string)$i.'</a></li>';
            } else {
                echo '<li><a href="manage.php?page='.$i.'">'.(string)$i.'</a></li>';
            }
        }

        if ($page_now == $page_total): ?>
        <li class="disabled">
            <a href="#" aria-label="Next"><span aria-hidden="true">&raquo;</span></a>
        </li>
        <?php else: ?>
        <li>
            <a href="manage.php?page=<?php echo $page_now+1; ?>" aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
            </a>
        </li>
        <?php endif; ?>
     </ul>
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
