<?php
/**
 * @Author: printempw
 * @Date:   2016-01-17 13:55:20
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-03-19 15:33:43
 */
session_start();
$dir = dirname(__FILE__);
require "$dir/includes/autoloader.php";
Database\Database::checkConfig();

// Auto load cookie value to session
if (isset($_COOKIE['uname']) && isset($_COOKIE['token'])) {
    $user = new User($_COOKIE['uname']);
    if ($_COOKIE['token'] == $user->getToken()) {
        $_SESSION['uname'] = $_COOKIE['uname'];
        $_SESSION['token'] = $user->getToken();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo Config::get('site_name'); ?></title>
    <link rel="shortcut icon" href="./assets/images/favicon.ico">
    <link rel="stylesheet" href="./libs/pure/pure-min.css">
    <link rel="stylesheet" href="./libs/pure/grids-responsive-min.css">
    <link rel="stylesheet" href="./assets/css/style.css">
    <link rel="stylesheet" href="./assets/css/index.style.css">
    <link rel="stylesheet" href="./libs/remodal/remodal.css">
    <link rel="stylesheet" href="./libs/ply/ply.css">
    <link rel="stylesheet" href="./libs/remodal/remodal-default-theme.css">
    <style>
        .home-menu-bg, .container {
            background-image: url("<?php echo Config::get('home_pic_url'); ?>");
        }
    </style>
</head>
<body>

<div class="header">
    <div class="home-menu pure-menu pure-menu-horizontal pure-menu-fixed">
        <a class="pure-menu-heading" href="<?php echo Config::get('site_url'); ?>">
            <?php echo Config::get('site_name'); ?>
        </a>
        <ul class="pure-menu-list">
            <li class="pure-menu-item">
                <?php if (isset($_SESSION['uname'])): ?>
                <a href="./user/index.php" class="pure-menu-link">
                    欢迎，<?php echo $_SESSION['uname']; ?>！
                </a>|<span class="pure-menu-link" id="logout">登出？</span>
                <?php elseif (Config::get('user_can_register') == 1): ?>
                <button id="login" class="pure-button pure-button-primary">登录</button>
                <?php endif; ?>
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
        <h1 class="splash-head"><?php echo Config::get('site_name'); ?></h1>
        <p class="splash-subhead">
            <?php echo Config::get('site_description'); ?>
        </p>
        <p>
        <?php if (!isset($_SESSION['uname'])):
            if (Config::get('user_can_register') == 1): ?>
            <button id="register" class="pure-button pure-button-primary">现在注册</button><?php
            else: ?>
            <button id="login" class="pure-button pure-button-primary">登录</button><?php
            endif; ?>
        <?php endif; ?>
        </p>
    </div>
</div>

<div class="footer">
    &copy; <a class="copy" href="https://prinzeugen.net">Blessing Studio</a> 2016
</div>

<!-- Contents below is for login/register dialog -->
<div class="remodal" data-remodal-id="login-modal">
    <button data-remodal-action="close" class="remodal-close"></button>
    <h1 id="login-title">登录</h1>
    <div class="pure-form">
        <input class="pure-input" id="uname" type="text" placeholder="用户名">
        <input class="pure-input" id="passwd" type="password" placeholder="密码">
        <br />
        <label for="keep" id="keep-label">
            <input id="keep" type="checkbox"> 记住我
        </label>
        <button id="login-button" class="pure-button pure-button-primary">登录</button>
    </div>
    <div id="msg" class="alert"></div>
</div>
<?php if (Config::get('user_can_register') == 1): ?>
<div class="remodal" data-remodal-id="register-modal">
    <button data-remodal-action="close" class="remodal-close"></button>
    <h1 id="register-title">注册</h1>
    <div class="pure-form">
        <input class="pure-input" id="reg-uname" type="text" placeholder="用户名">
        <input class="pure-input" id="reg-passwd" type="password" placeholder="密码">
        <input class="pure-input" id="reg-passwd2" type="password" placeholder="确认密码">
        <br />
        <button id="register-button" class="pure-button pure-button-primary">注册</button>
    </div>
    <div id="msg" class="alert alert-info">请使用您的 <b>Minecraft 用户名</b> 来注册</div>
</div>
<?php endif; ?>
<!-- Contents above is for login/register dialog -->

<script type="text/javascript" src="./libs/jquery/jquery-2.1.1.min.js"></script>
<script type="text/javascript" src="./libs/cookie.js"></script>
<script type="text/javascript" src="./libs/remodal/remodal.min.js"></script>
<script type="text/javascript" src="./libs/ply/ply.min.js"></script>
<script type="text/javascript" src="./assets/js/utils.js"></script>
<script type="text/javascript" src="./assets/js/index.utils.js"></script>
<?php if ($msg = Utils::getValue('msg', $_GET)): ?>
<script type="text/javascript"> showAlert("<?php echo $msg; ?>"); </script>
<?php endif; ?>
</body>
</html>
