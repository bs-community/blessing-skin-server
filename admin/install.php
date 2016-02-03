<?php
/**
 * @Author: printempw
 * @Date:   2016-01-16 23:01:33
 * @Last Modified by:   prpr
 * @Last Modified time: 2016-02-03 15:51:27
 *
 * Create tables automatically
 */

$dir = dirname(dirname(__FILE__));
require "$dir/includes/autoload.inc.php";
require "$dir/config.php";

echo "<style>body { font-family: Courier; }</style>";

if (!file_exists("./install.lock")) {
	$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWD, DB_NAME);

	echo "<h2>Blessing Skin Server Install</h2>";

	if ($conn->connect_error) {
		utils::raise(-1, "Can not connect to mysql, check if database info correct in config.php. ".$conn->connect_error);
	} else {
		echo "Succesfully connected to database ".DB_USER."@".DB_HOST.". <br /><br />";
	}

	echo "Start creating tables... <br /><br />";

	$sql  =  "CREATE TABLE IF NOT EXISTS `users` (
			  `uid` int(11) NOT NULL AUTO_INCREMENT,
			  `username` varchar(20) NOT NULL,
			  `password` varchar(32) NOT NULL,
			  `ip` varchar(32) NOT NULL,
			  `preference` varchar(10) NOT NULL,
			  `skin_hash` varchar(64) NOT NULL,
			  `cape_hash` varchar(64) NOT NULL,
			  `last_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			  PRIMARY KEY (`uid`)
			) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=15;";

	if(!$conn->query($sql)) {
		die("Creating tables failed. <br /><br />".$conn->error);
	}

	/**
	 * username: admin
	 * password: 123456
	 */
	$conn->query("INSERT INTO `users` (`uid`, `username`, `password`, `ip`, `preference`) VALUES(1, 'admin', 'e10adc3949ba59abbe56e057f20f883e', '127.0.0.1', 'default')");

	echo "Creating tables successfully <br /><br />";

	echo "
<pre style='font-family: Courier;'>
+-----------------------------------------------------------------------------------+-----------------+
|  uid  |  username  |  password  |  ip  |  preference  |  skin_hash  |  cape_hash  |  last_modofied  |
+-----------------------------------------------------------------------------------+-----------------+
|   1   |    admin   |   123456   |   *  |    default   |      *      |      *      |        *        |
+-----------------------------------------------------------------------------------+-----------------+
</pre>
	";

	if (!is_dir("../textures/")) {
		echo mkdir("../textures/") ? "Creating textures directory...<br /><br />" :
								 	 "Creating textures directory failed. Check permissons.<br /><br />";
	}

	echo "Successfully installed. <a href='../index.php'>Index</a>";

	if ($lock = fopen("./install.lock", 'w')) {
		fwrite($lock, time());
		fclose($lock);
	} else {
		die("Unable to write `install.lock`. Please check the permisson and create a `install.lock` file manually.");
	}

} else {
	echo "<br />";
	echo "It seems that you have already installed. <a href='../index.php'>Index</a><br /><br />";
	echo "May you should delete the file `install.lock` in ./admin to unlock installing.";
}
?>
