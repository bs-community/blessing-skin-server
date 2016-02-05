<?php
/*
* @Author: prpr
* @Date:   2016-01-21 13:56:40
* @Last Modified by:   prpr
* @Last Modified time: 2016-01-21 20:40:04
*/
session_start();
$dir = dirname(dirname(__FILE__));
require "$dir/includes/autoload.inc.php";

$action = isset($_GET['action']) ? $_GET['action'] : "";

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
    <title>Upload - Blessing Skin Server</title>
    <link rel="shortcut icon" href="../assets/images/favicon.ico">
    <link rel="stylesheet" href="../libs/pure/pure-min.css">
    <link rel="stylesheet" href="../libs/pure/grids-responsive-min.css">
    <link rel="stylesheet" href="../libs/glyphicon/glyphicon.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/user.style.css">
    <link rel="stylesheet" href="../libs/ply/ply.css">
</head>
<body>
<div class="header">
    <div class="home-menu pure-menu pure-menu-horizontal pure-menu-fixed">
        <a class="pure-menu-heading" href="../index.php">Blessing Skin Server</a>
        <ul class="pure-menu-list">
             <li class="pure-menu-item">
                 <a class="pure-menu-link" href="profile.php">Profile</a>
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
        <div class="pure-u-md-1-2 pure-u-1">
            <div class="panel panel-default">
                <div class="panel-heading">Upload</div>
                <div class="panel-body">
                    <div id="upload-form">
                        <p>Select a skin:</p>
                        <input type="file" id="skininput" name="skininput" accept="image/png" />
                        <br />
                        <p>Select a cape:</p>
                        <input type="file" id="capeinput" name="capeinput" accept="image/png" />
                        <br />
                        <input type="radio" id="model" name="model" />My skin fits on the classic Steve player model.
                        <br />
                        <input type="radio" id="model" name="model" />My skin fits on the new Alex player model.
                        <br /><br />
                        <button id="upload" class="pure-button pure-button-primary">Upload</button>
                        <a href="javascript:show2dPreview();" class="pure-button">2D Preview</a>
                    </div>
                    <div id="msg" class="alert hide" role="alert"></div>
                </div>
            </div>
        </div>
        <div class="pure-u-md-1-2 pure-u-1">
            <div class="panel panel-default">
                <div class="panel-heading">Preview
                    <div class="operations">
                        <span title="Movements" class="glyphicon glyphicon-pause"></span>
                        <span title="Running" class="glyphicon glyphicon-forward"></span>
                        <span title="Rotation" class="glyphicon glyphicon-repeat"></span>
                    </div>
                </div>
                <div class="panel-body">
                    <?php include "preview.php"; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
<script type="text/javascript" src="../libs/jquery/jquery-2.1.1.min.js"></script>
<script type="text/javascript" src="../libs/ply/ply.min.js"></script>
<script type="text/javascript" src="../libs/cookie.js"></script>
<script type="text/javascript" src="../assets/js/utils.js"></script>
<script type="text/javascript" src="../assets/js/user.utils.js"></script>

</html>
