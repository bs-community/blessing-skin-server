<?php
/**
 * @Author: prpr
 * @Date:   2016-02-03 16:12:45
 * @Last Modified by:   prpr
 * @Last Modified time: 2016-02-04 23:11:11
 */

session_start();
$dir = dirname(dirname(__FILE__));
require "$dir/includes/autoload.inc.php";

if(isset($_COOKIE['uname']) && isset($_COOKIE['token'])) {
    $_SESSION['uname'] = $_COOKIE['uname'];
    $_SESSION['token'] = $_COOKIE['token'];
}

if (isset($_SESSION['uname'])) {
    $user = new user($_SESSION['uname']);
    if ($_SESSION['token'] != $user->getToken()) {
        header('Location: ../index.php?msg=Invalid token. Please login.');
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
    <title>Profile - Blessing Skin Server</title>
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
        <a class="pure-menu-heading" href="../index.php">Blessing Skin Server</a>
        <a href="javascript:;" title="Movements"><span class="glyphicon glyphicon-pause"></span></a>
        <a href="javascript:;" title="Running"><span class="glyphicon glyphicon-forward"></span></a>
        <a href="javascript:;" title="Rotation"><span class="glyphicon glyphicon-repeat"></span></a>
        <ul class="pure-menu-list">
            <li class="pure-menu-item">
                <a class="pure-menu-link" href="index.php">Upload</a>
            </li>
            <li class="pure-menu-item">
                <span class="pure-menu-link">Welcome, <?php echo $_SESSION['uname']; ?>!</span> | <span class="pure-menu-link" id="logout">Log out?</span>
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
                <div class="panel-heading">Change Password</div>
                <div class="panel-body">
                    <div class="pure-form pure-form-stacked">
                        <input id="passwd" type="password" placeholder="Old password">
                        <input id="new-passwd" type="password" placeholder="New password">
                        <input id="confirm-pwd" type="password" placeholder="Repeat to confirm">
                        <button id="change" class="pure-button pure-button-primary">Change Password</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="pure-u-1 pure-u-md-1-2">
            <div class="panel panel-danger">
                <div class="panel-heading">Delete Account</div>
                <div class="panel-body">
                    <p>Are you sure you want to delete your account?</p>
                    <p>You're about to delete your account on Blessing Skin Server.</p>
                    <p>This is permanent! No backups, no restores, no magic undo button.</p>
                    <p>We warned you, ok?</p>
                    <button id="delete" class="pure-button pure-button-error">I am sure.</button>
                </div>
            </div>
        </div>
    </div>
    <div class="pure-g">
        <div class="pure-u-1 pure-u-md-1-2">
            <div class="panel panel-default">
                <div class="panel-heading">How To Use?</div>
                <div class="panel-body">
                    <p>Check it here: <a href="https://github.com/printempw/blessing-skin-server/blob/master/README.md">printempw/blessing-skin-server</a></p>
                </div>
            </div>
        </div>
        <?php if ($user->is_admin) { ?>
        <div class="pure-u-1 pure-u-md-1-2">
            <div class="panel panel-default">
                <div class="panel-heading">Welcome, administrator.</div>
                <div class="panel-body">
                    <p>Here manage your site: <a href="../admin/">Console</a></p>
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
