<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Blessing Skin Server</title>
    <link rel="stylesheet" href="./libs/pure/pure-min.css">
    <link rel="stylesheet" href="./libs/pure/grids-responsive-min.css">
    <link rel="stylesheet" href="./assets/css/style.css">
    <link rel="stylesheet" href="./libs/remodal/remodal.css">
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
                <a id="login" href="#login-modal" class="pure-button pure-button-primary">Sign In</a>
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
        <p>
            <a id="register" href="#register-modal" class="pure-button pure-button-primary">Sign Up</a>
        </p>
    </div>
</div>

<div class="footer">
    &copy; <a class="copy" href="https://prinzeugen.net">Blessing Studio</a> 2016
</div>

<div class="remodal" data-remodal-id="login-modal">
    <button data-remodal-action="close" class="remodal-close"></button>
    <h1 id="login-title">Sign In</h1>
    <form class="pure-form">
        <input class="pure-input" id="uname" type="text" placeholder="Username">
        <input class="pure-input" id="passwd" type="password" placeholder="Password">
        <br />
        <label for="keep" id="keep-label">
            <input id="keep" type="checkbox"> Remember me
        </label>
        <button id="login-button" class="pure-button pure-button-primary">Sign In</button>
    </form>
    <div id="msg" class="alert"></div>
</div>

<div class="remodal" data-remodal-id="register-modal">
    <button data-remodal-action="close" class="remodal-close"></button>
    <h1 id="register-title">Sign Up</h1>
    <form class="pure-form">
        <input class="pure-input" id="reg-uname" type="text" placeholder="Username">
        <input class="pure-input" id="reg-passwd" type="password" placeholder="Password">
        <input class="pure-input" id="reg-passwd2" type="password" placeholder="Comfirm Password">
        <br />
        <button id="register-button" class="pure-button pure-button-primary">Sign Up</button>
    </form>
    <div id="msg" class="alert"></div>
</div>

<script src="./libs/jquery/jquery-2.1.1.min.js"></script>
<script src="./libs/cookie.js"></script>
<script src="./libs/remodal/remodal.min.js"></script>
<script src="./assets/js/index.utils.js"></script>

</body>
</html>
