<?php
/**
 * @Author: printempw
 * @Date:   2016-01-09 21:11:53
 * @Last Modified by:   prpr
 * @Last Modified time: 2016-01-17 14:26:29
 */
session_start();
function __autoload($classname) {
	$dir = dirname(__FILE__);
    $filename = "$dir/includes/". $classname .".class.php";
    include_once($filename);
}

if (getValue('uname', $_COOKIE) && getValue('token', $_COOKIE)) {
	$user = new user($_COOKIE['uname']);
	if ($_COOKIE['token'] == $user -> getToken()) {
		$_SESSION['uname'] = $_COOKIE['uname'];
		$_SESSION['token'] = $user -> getToken();
	}
}

function getValue($key, $array) {
	if (array_key_exists($key, $array)) {
		return $array[$key];
	}
	return false;
}

function echoScript($script) {
	echo "<script>".$script."</script>";
}
?>
<!DOCTYPE HTML>
<html>
<head>
	<title>Index - Blessing Skin Server 0.1</title>
	<link rel="stylesheet" href="./libs/bootstrap/bootstrap.min.css">
	<link rel="stylesheet" href="./assets/css/style.css">
</head>

<body>
<header>
	<nav class="navbar navbar-default">
		<div class="container-fluid">
			<div class="navbar-header">
				<a style="font-family:Minecraft;" class="navbar-brand" href="./index.php">Blessing Skin Server</a>
			</div>
			<div class="collapse navbar-collapse">
				<!-- <ul class="nav navbar-nav">
				<li><a href="#">Link</a></li>
				</ul> -->
				<ul class="nav navbar-nav navbar-right">
					<li><a id="login-reg" href="javascript:;"><?php
		    				if (getValue('action', $_GET) == "register") {
		    				    echo "Login";
		    				} else {
		    				    echo "Register";
		    				}
					?></a></li>
				</ul>
			</div><!-- /.navbar-collapse -->
		</div><!-- /.container-fluid -->
	</nav>
</header>
<div class="main">
<img class="l" src="./assets/images/skin.png" />
<div class="login-container r">
	<h2 class="login-title">Log in</h2>
		<div id="login-form">
			<div class="input-group">
				<span class="input-group-addon"><span class="glyphicon glyphicon-user"></span></span>
				<input name="username" id="uname" type="text" class="form-control" placeholder="Username">
			</div>
			<div class="input-group">
				<span class="input-group-addon"><span class="glyphicon glyphicon-lock"></span></span>
				<input name="password" id="passwd" type="password" class="form-control" placeholder="Password">
			</div>
			<div id="confirm-passwd" style="display:none;" class="input-group">
				<span class="input-group-addon"><span class="glyphicon glyphicon-ok"></span></span>
				<input name="comfirm-passwd" id="cpasswd" type="password" class="form-control" placeholder="Confirm Password">
			</div>
			<div class="login-group">
				<div class="checkbox-wrapper">
			    		<input id="keep" type="checkbox" class="checkbox">
			    		<label for="keep" class="checkbox-label"></label><span>   Remember me</span>
				</div>
				<button id="login" type="button" class="btn btn-default">Log in</button>
			</div>
		</div>
	<div id="msg-container">
		<div id="msg" class="alert hide" role="alert" />
	</div>
</div>
</div>
<footer>
<p>© <a class="copy" href="https://prinzeugen.net">Blessing Studio</a> 2015</p>
</footer>
</body>
<script type="text/javascript" src="./libs/jquery/jquery-1.9.1.min.js"></script>
<script type="text/javascript" src="./libs/cookie.js"></script>
<script type="text/javascript" src="./assets/js/login_utils.js"></script>
<?php
if (getValue('action', $_GET) == "register") {
    echoScript("changeForm(1);");
}
if ($msg = getValue('msg', $_GET)) {
    echoScript("showMsg('alert-warning','".$msg."');");
}

if (getValue('uname', $_SESSION)) {
	echoScript("$('.login-title').html('Welcome');");
	echoScript("$('#login-form').html('<a href=\"./user/index.php\">User Center</a>');");
	echoScript("$('.navbar-right').html('<li><a href=\"javascript:;\">Welcome,".$_SESSION['uname']."!</a><li>');");
}
?>
<script type="text/javascript" src="./assets/js/ajax.js"></script>
</html>
