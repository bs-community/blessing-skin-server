<?php
if (!file_exists("./install.lock")) {
	require "../config.php";
	$con = mysql_connect(DB_HOST, DB_USER, DB_PASSWD);

	echo "<h2>Blessing Skin Server Install</h2>";

	if (!$con) {
	    die ("Can not connect to mysql, check if database info correct in config.php. ".mysql_error());
	} else {
		echo "Succesfully connected to mysql server.<br /><br />";
	}

	if(!mysql_select_db(DB_NAME, $con)){
		die("Can not select database, please check if database '".DB_NAME."' really exists.");
	}

	echo "Selected database: ".DB_NAME."<br /><br />";

	echo "Start creating tables... <br /><br />";

	$query = "CREATE TABLE IF NOT EXISTS `users` (
			  `uid` int(11) NOT NULL AUTO_INCREMENT,
			  `admin` tinyint(1) NOT NULL DEFAULT '0',
			  `username` varchar(20) NOT NULL,
			  `password` varchar(32) NOT NULL,
			  `ip` varchar(32) NOT NULL,
			  PRIMARY KEY (`uid`),
			  UNIQUE KEY `uid` (`uid`)
			) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=15;";

	if(!mysql_query($query)) {
		die("Creating tables failed. ".mysql_error());
	}

	/**
	 * username: admin
	 * password: 123456
	 */
	mysql_query("INSERT INTO `users` (`uid`, `admin`, `username`, `password`, `ip`) VALUES(1, 1, 'admin', 'e10adc3949ba59abbe56e057f20f883e', '')");

	echo "Successfully installed. <a href='../index.php'>Index</a>";

	$lock = fopen("./install.lock", w) or die("Unable to write 'install.lock'.");
	fwrite($lock, time());
	fclose($lock);

} else {
	echo "It seems that you have already installed. <a href='../index.php'>Index</a><br /><br />";
	echo "May you should delete the file 'install.lock' in /admin to unlock installing.";
}
?>
