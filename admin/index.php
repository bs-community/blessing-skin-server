<?php
/**
 * @Author: prpr
 * @Date:   2016-02-03 14:39:50
 * @Last Modified by:   prpr
 * @Last Modified time: 2016-02-04 18:08:41
 */

session_start();
$dir = dirname(dirname(__FILE__));
require "$dir/includes/autoload.inc.php";
require "$dir/config.php";

if(isset($_COOKIE['uname']) && isset($_COOKIE['token'])) {
    $_SESSION['uname'] = $_COOKIE['uname'];
    $_SESSION['token'] = $_COOKIE['token'];
}

if (isset($_SESSION['uname'])) {
    $admin = new user($_SESSION['uname']);
    if ($_SESSION['token'] != $admin->getToken()) {
        header('Location: ../index.php?msg=Invalid token. Please login.');
    } else if (!$admin->is_admin) {
        header('Location: ../index.php?msg=Looks like that you are not administrator :(');
    }
} else {
    header('Location: ../index.php?msg=Illegal access. Please login.');
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Console - Blessing Skin Server</title>
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
        <a class="pure-menu-heading" href="../index.php">Blessing Skin Server</a>
        <ul class="pure-menu-list">
            <li class="pure-menu-item">
                <a class="pure-menu-link" href="../user/profile.php">Profile</a>
            </li>
            <li class="pure-menu-item">
                    <a href="javascript:;" class="pure-menu-link">Welcome, <?php echo $_SESSION['uname']; ?>!</a>
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
                <th>Username</th>
                <th>Preview Textures</th>
                <th>Change Textures</th>
                <th>Opreation</th>
            </tr>
        </thead>

        <tbody>
            <?php
            $db = new database();
            $result = $db->query("SELECT * FROM users");
            while ($row = $result->fetch_array()) { ?>
            <tr>
                <td><?php echo $row['uid']; ?></td>
                <td><?php echo $row['username']; ?></td>
                <td>
                    <?php echo '<img id="'.$row['username'].'_skin" width="64" '.(($row['skin_hash'] == "") ? '' : 'src="../skin/'.$row['username'].'.png"').'/>'; ?>
                    <?php echo '<img id="'.$row['username'].'_cape" width="64" '.(($row['cape_hash'] == "") ? '' : 'src="../cape/'.$row['username'].'.png"').'/>'; ?>
                </td>
                <td>
                    <a href="javascript:showUpload('<?php echo $row['username']; ?>', 'skin');" class="pure-button pure-button-primary">Skin</a>
                    <a href="javascript:showUpload('<?php echo $row['username']; ?>', 'cape');" class="pure-button pure-button-primary">Cape</a>
                    <a href="javascript:showModel('<?php echo $row['username']; ?>', 'cape');" class="pure-button pure-button-default">Model</a>
                    <span>(<?php echo $row['preference']; ?>)</span>
                </td>
                <td>
                    <a href="javascript:showChange('<?php echo $row['username'] ?>');" class="pure-button pure-button-default">Password</a>
                    <a href="javascript:showDelete('<?php echo $row['username'] ?>');" class="pure-button pure-button-error">Delete</a>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<div class="footer">
    &copy; <a class="copy" href="https://prinzeugen.net">Blessing Studio</a> 2016
</div>

</body>
<script type="text/javascript" src="../libs/jquery/jquery-2.1.1.min.js"></script>
<script type="text/javascript" src="../libs/ply/ply.min.js"></script>
<script type="text/javascript" src="../assets/js/utils.js"></script>
<script type="text/javascript" src="../assets/js/admin.utils.js"></script>
</html>
</body>
</html>
