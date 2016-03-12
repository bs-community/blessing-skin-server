<?php
/**
 * @Author: printempw
 * @Date:   2016-01-16 23:01:33
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-03-12 16:44:47
 *
 * Create tables automatically
 */

$dir = dirname(dirname(__FILE__));
require "$dir/includes/autoload.inc.php";
header('Content-type: text/html; charset=utf-8');

echo "<style>body { font-family: Courier, 'Microsoft Yahei', 'Microsoft Jhenghei', sans-serif; }</style>";

if (!file_exists("./install.lock")) {
	$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWD, DB_NAME, DB_PORT);

	echo "<h2>Blessing Skin Server 安装程序</h2>";

	if ($conn->connect_error) {
		Utils::raise(-1, "无法连接至 MySQL 服务器，确定你在 config.php 填写的数据库信息正确吗？".$conn->connect_error);
	} else {
		echo "成功连接至 MySQL 服务器 ".DB_USER."@".DB_HOST."。 <br /><br />";
	}

	echo "开始创建数据表。。 <br /><br />";

	$sql  =  "CREATE TABLE IF NOT EXISTS `users` (
			  `uid` int(11) NOT NULL AUTO_INCREMENT,
			  `username` varchar(20) NOT NULL,
			  `password` varchar(32) NOT NULL,
			  `ip` varchar(32) NOT NULL,
			  `preference` varchar(10) NOT NULL,
			  `hash_steve` varchar(64) NOT NULL,
			  `hash_alex` varchar(64) NOT NULL,
			  `hash_cape` varchar(64) NOT NULL,
			  `last_modified` datetime NOT NULL,
			  PRIMARY KEY (`uid`)
			) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=15;";

	if(!$conn->query($sql)) {
		die("数据表创建失败了。。请带上错误信息联系作者 <br /><br />".$conn->error);
	}

	/**
	 * username: admin
	 * password: 123456
	 */
	$conn->query("INSERT INTO `users` (`uid`, `username`, `password`, `ip`, `preference`) VALUES(1, 'admin', 'e10adc3949ba59abbe56e057f20f883e', '127.0.0.1', 'default')");

	echo "数据表创建成功！ <br /><br />";

	echo "
<pre style='font-family: Courier;'>
+-------------------------------------------------------------------------------------------------+-----------------+
|  uid  |  username  |  password  |  ip  |  preference  |  hash_steve |  hash_alex  |  hash_cape  |  last_modofied  |
+-------------------------------------------------------------------------------------------------+-----------------+
|   1   |    admin   |   123456   |   *  |    default   |      *      |      *      |      *      |        *        |
+-------------------------------------------------------------------------------------------------+-----------------+
</pre>
	";

	if (!is_dir("../textures/")) {
		echo mkdir("../textures/") ? "正在创建 textures 文件夹。。<br /><br />" :
								 	 "文件夹创建失败。确定你的目录权限正确吗？<br /><br />";
	}

	echo "安装成功辣~ <a href='../index.php'>首页</a>";

	if ($lock = fopen("./install.lock", 'w')) {
		fwrite($lock, time());
		fclose($lock);
	} else {
		die("无法创建自动 `install.lock`。请人工新建一个 `install.lock` 置于 `admin` 目录下。");
	}

} else {
	echo "<br />";
	echo "看起来你已经安装过一次了哦？ <a href='../index.php'>首页</a><br /><br />";
	echo "或许你需要删除 `admin` 文件夹下的 `install.lock` 开解锁安装程序。";
}
?>
