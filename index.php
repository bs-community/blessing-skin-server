<?php
	require "./connect.php";
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
    				if ($_GET["action"] == "register") {
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
if ($_GET["action"] == "register") {
    echo "<script>changeForm(1);</script>";
}
if ($_GET["msg"]) {
    echo "<script>showMsg('alert-warning','".$_GET['msg']."');</script>";
}?>
<script type="text/javascript" src="./assets/js/ajax.js"></script>
</html>
