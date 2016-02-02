<?php
/**
 * @Author: printempw
 * @Date:   2016-01-17 13:55:20
 * @Last Modified by:   prpr
 * @Last Modified time: 2016-02-02 21:20:23
 */
session_start();
$dir = dirname(__FILE__);
require "$dir/includes/autoload.inc.php";
// Auto load cookie value to session
if (isset($_COOKIE['uname']) && isset($_COOKIE['token'])) {
    $user = new user($_COOKIE['uname']);
    if ($_COOKIE['token'] == $user->getToken()) {
        $_SESSION['uname'] = $_COOKIE['uname'];
        $_SESSION['token'] = $user->getToken();
    }
} ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blessing Skin Server</title>
    <link rel="stylesheet" href="./libs/pure/pure-min.css">
    <link rel="stylesheet" href="./libs/pure/grids-responsive-min.css">
    <link rel="stylesheet" href="./assets/css/style.css">
    <link rel="stylesheet" href="./assets/css/index.style.css">
    <link rel="stylesheet" href="./libs/remodal/remodal.css">
    <link rel="stylesheet" href="./libs/ply/ply.css">
    <link rel="stylesheet" href="./libs/remodal/remodal-default-theme.css">
</head>
<body>

<div class="header">
    <div class="home-menu pure-menu pure-menu-horizontal pure-menu-fixed">
        <a class="pure-menu-heading" href="#">Blessing Skin Server</a>
        <ul class="pure-menu-list">
            <li class="pure-menu-item pure-menu-selected">
                <a href="#" class="pure-menu-link">Home</a>
            </li>
            <li class="pure-menu-item">
                <?php if ($uname = utils::getValue('uname', $_SESSION)) { ?>
                <a href="./user/index.php" class="pure-menu-link" style="color: #5e5e5e">Welcome, <?php echo $uname; ?></a>
                <?php } else { ?>
                <a id="login" href="javascript:;" class="pure-button pure-button-primary">Sign In</a>
                <?php } ?>
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
    <div class="splash">
        <h1 class="splash-head">Blessing Skin Server</h1>
        <p class="splash-subhead">
            Just a simple open-source Minecraft skin server
        </p>
        <?php
            if (!utils::getValue('uname', $_SESSION)) { ?>
        <p>
            <a id="register" href="javascript:;" class="pure-button pure-button-primary">Sign Up</a>
        </p>
        <?php } ?>
    </div>
</div>

<div class="footer">
    &copy; <a class="copy" href="https://prinzeugen.net">Blessing Studio</a> 2016
</div>

<div class="remodal" data-remodal-id="login-modal">
    <button data-remodal-action="close" class="remodal-close"></button>
    <h1 id="login-title">Sign In</h1>
    <div class="pure-form">
        <input class="pure-input" id="uname" type="text" placeholder="Username">
        <input class="pure-input" id="passwd" type="password" placeholder="Password">
        <br />
        <label for="keep" id="keep-label">
            <input id="keep" type="checkbox"> Remember me
        </label>
        <button id="login-button" class="pure-button pure-button-primary">Sign In</button>
    </div>
    <div id="msg" class="alert"></div>
</div>

<div class="remodal" data-remodal-id="register-modal">
    <button data-remodal-action="close" class="remodal-close"></button>
    <h1 id="register-title">Sign Up</h1>
    <div class="pure-form">
        <input class="pure-input" id="reg-uname" type="text" placeholder="Username">
        <input class="pure-input" id="reg-passwd" type="password" placeholder="Password">
        <input class="pure-input" id="reg-passwd2" type="password" placeholder="Comfirm Password">
        <br />
        <button id="register-button" class="pure-button pure-button-primary">Sign Up</button>
    </div>
    <div id="msg" class="alert"></div>
</div>

<script src="./libs/jquery/jquery-2.1.1.min.js"></script>
<script src="./libs/cookie.js"></script>
<script src="./libs/remodal/remodal.min.js"></script>
<script src="./libs/ply/ply.min.js"></script>
<script src="./assets/js/index.utils.js"></script>
<?php
if ($msg = utils::getValue('msg', $_GET)) { ?>
    <script type="text/javascript">
        showAlert("<?php echo $msg; ?>");
    </script>
<?php } ?>
</body>
</html>
