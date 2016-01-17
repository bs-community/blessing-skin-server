<?php
/**
 * @Author: printempw
 * @Date:   2016-01-16 23:01:33
 * @Last Modified by:   prpr
 * @Last Modified time: 2016-01-17 10:47:20
 *
 * Create tables automatically
 */

function __autoload($classname) {
    $filename = "./includes/". $classname .".class.php";
    include_once($filename);
}

echo "<style>body { font-family: Courier; }</style>";

if (!file_exists("./install.lock")) {
	require "../config.php";
	$con = mysql_connect(DB_HOST, DB_USER, DB_PASSWD);

	echo "<h2>Blessing Skin Server Install</h2>";

	if (!$con) {
		utils::raise('1', "Can not connect to mysql, check if database info correct in config.php. ".mysql_error());
	} else {
		echo "Succesfully connected to mysql server.<br /><br />";
	}

	if(!mysql_select_db(DB_NAME, $con)){
		utils::raise('1', "Can not select database, please check if database '".DB_NAME."' really exists.");
	}

	echo "Selected database: ".DB_NAME."<br /><br />";

	echo "Start creating tables... <br /><br />";

	$query = "CREATE TABLE IF NOT EXISTS `users` (
			  `uid` int(11) NOT NULL AUTO_INCREMENT,
			  `username` varchar(20) NOT NULL,
			  `password` varchar(32) NOT NULL,
			  `ip` varchar(32) NOT NULL,
			  `preference` varchar(32) NOT NULL,
			  `skin_hash` varchar(32) NOT NULL,
			  `cape_hash` varchar(32) NOT NULL,
			  PRIMARY KEY (`uid`),
			  UNIQUE KEY `uid` (`uid`)
			) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=15;";

	if(!mysql_query($query)) {
		utils::raise('1', "Creating tables failed. ".mysql_error());
	}

	/**
	 * username: admin
	 * password: 123456
	 */
	mysql_query("INSERT INTO `users` (`uid`, `username`, `password`, `ip`, `preference`) VALUES(1, 'admin', 'e10adc3949ba59abbe56e057f20f883e', '127.0.0.1', 'default')");

	echo "Creating tables successfully <br /><br />";

	echo "
<pre style='font-family: Courier;'>
+-----------------------------------------------------------------------------------+
|  uid  |  username  |  password  |  ip  |  preference  |  skin_hash  |  cape_hash  |
+-----------------------------------------------------------------------------------+
|   1   |    admin   |   123456   |   *  |    default   |      *      |      *      |
+-----------------------------------------------------------------------------------+
</pre>
	";

	echo "Successfully installed. <a href='../index.php'>Index</a>";

	if ($lock = fopen("./install.lock", 'w')) {
		fwrite($lock, time());
		fclose($lock);
	} else {
		die("Unable to write 'install.lock'.");
	}

} else {
	echo "<br />";
	echo "It seems that you have already installed. <a href='../index.php'>Index</a><br /><br />";
	echo "May you should delete the file 'install.lock' in ./admin to unlock installing.";
}
?>
