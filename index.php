<?php
/**
 * @Author: printempw
 * @Date:   2016-01-17 13:55:20
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-04-11 17:01:15
 */
session_start();
$dir = dirname(__FILE__);
require "$dir/libraries/autoloader.php";
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
    <title><?php echo Option::get('site_name'); ?></title>
    <link rel="shortcut icon" href="./assets/images/favicon.ico">
    <link rel="stylesheet" href="./assets/libs/pure/pure-min.css">
    <link rel="stylesheet" href="./assets/libs/pure/grids-responsive-min.css">
    <link rel="stylesheet" href="../assets/libs/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="./assets/css/style.css">
    <link rel="stylesheet" href="./assets/css/index.style.css">
    <link rel="stylesheet" href="./assets/libs/remodal/remodal.css">
    <link rel="stylesheet" href="./assets/libs/ply/ply.css">
    <link rel="stylesheet" href="./assets/libs/remodal/remodal-default-theme.css">

    <?php if (Option::get('google_font_cdn') == "google"): ?>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Ubuntu">
    <?php elseif (Option::get('google_font_cdn') == "moefont"): ?>
    <link rel="stylesheet" href="https://cdn.moefont.com/fonts/css?family=Ubuntu">
    <?php elseif (Option::get('google_font_cdn') == "useso"): ?>
    <link rel="stylesheet" href="http://fonts.useso.com/css?family=Ubuntu">
    <?php endif; ?>

    <style>
        .home-menu-bg, .container {
            background-image: url("<?php echo Option::get('home_pic_url'); ?>");
        }
    </style>
    <style><?php echo Option::get('custom_css'); ?></style>
</head>
<body>

<div class="header">
    <div class="home-menu pure-menu pure-menu-horizontal pure-menu-fixed">
        <a class="pure-menu-heading" href="<?php echo Option::get('site_url'); ?>">
            <?php echo Option::get('site_name'); ?>
        </a>
        <ul class="pure-menu-list">
            <li class="pure-menu-item">
                <?php if (isset($_SESSION['uname'])): ?>
                <a href="./user/index.php" class="pure-menu-link">
                    欢迎，<?php echo $_SESSION['uname']; ?>！
                </a>|<span class="pure-menu-link" id="logout">登出？</span>
                <?php elseif (Option::get('user_can_register') == 1): ?>
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
        <h1 class="splash-head"><?php echo Option::get('site_name'); ?></h1>
        <p class="splash-subhead">
            <?php echo Option::get('site_description'); ?>
        </p>
        <p>
        <?php if (!isset($_SESSION['uname'])):
            if (Option::get('user_can_register') == 1): ?>
            <button id="register" class="pure-button pure-button-primary">现在注册</button><?php
            else: ?>
            <button id="login" class="pure-button pure-button-primary">登录</button><?php
            endif; ?>
        <?php else: ?>
            <a href="./user/" class="pure-button pure-button-primary">用户中心</a>
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
    <div id="msg" class="alert hide"></div>
</div>
<?php if (Option::get('user_can_register') == 1): ?>
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

<script type="text/javascript" src="./assets/libs/jquery/jquery-2.1.1.min.js"></script>
<script type="text/javascript" src="./assets/libs/cookie.js"></script>
<script type="text/javascript" src="./assets/libs/remodal/remodal.min.js"></script>
<script type="text/javascript" src="./assets/libs/ply/ply.min.js"></script>
<script type="text/javascript" src="./assets/js/utils.js"></script>
<script type="text/javascript" src="./assets/js/index.utils.js"></script>
<script><?php echo Option::get('custom_js'); ?></script>

<?php if (isset($_GET['msg'])): ?>
<script type="text/javascript"> showAlert("<?php echo $_GET['msg']; ?>"); </script>
<?php endif; ?>
</body>
</html>
