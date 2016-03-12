<?php
/**
 * @Author: printempw
 * @Date:   2016-02-02 21:59:06
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-03-12 20:55:08
 */

class Database
{
    private $connection = null;

    function __construct() {
        $this->connection = self::checkConfig();
    }

    public static function checkConfig() {
        if (!DEBUG_MODE) error_reporting(0);
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWD, DB_NAME, DB_PORT);
        if ($conn->connect_error) {
            Utils::showErrorPage($conn->connect_errno,
                "无法连接至 MySQL 服务器。请确认 config.php 中的配置是否正确：".$conn->connect_error);
        }
        if (!self::checkTableExist($conn)) {
            Utils::showErrorPage(-1, "数据库中不存在 users 表。请先运行 /admin/install.php 进行安装。");
        }
        $dir = dirname(dirname(__FILE__));
        if (!is_dir("$dir/textures/")) {
            Utils::showErrorPage(-1, "textures 文件夹不存在。请先运行 /admin/install.php 进行安装，或者手动放置一个。");
        }
        return $conn;
    }

    public static function checkTableExist($conn) {
        $sql = "SELECT table_name FROM
                 `INFORMATION_SCHEMA`.`TABLES` WHERE table_name ='users'
                 AND TABLE_SCHEMA='".DB_NAME."'";
        return ($conn->query($sql)->num_rows != 0) ? true : false;
    }

    public function query($sql) {
        $result = $this->connection->query($sql);
        if (!$this->connection->error) {
            return $result;
        }
        Utils::raise(-1, "Database query error: ".$this->connection->error);
    }

    public function fetchArray($sql) {
        return $this->query($sql)->fetch_array();
    }

    public function select($key, $value) {
        return $this->fetchArray("SELECT * FROM users WHERE $key='$value'");
    }

    public function getNumRows($key, $value) {
        $sql = "SELECT * FROM users WHERE $key='$value'";
        return $this->query($sql)->num_rows;
    }

    public function getRecordNum() {
        $sql = "SELECT * FROM users WHERE 1";
        return $this->query($sql)->num_rows;
    }

    public function checkRecordExist($key, $value) {
        return ($this->getNumRows($key, $value) != 0) ? true : false;
    }

    public function insert($array) {
        $uname  = $array['uname'];
        $passwd = $array['passwd'];
        $ip = $array['ip'];
        $sql = "INSERT INTO users (username, password, ip, preference)
                             VALUES ('$uname', '$passwd', '$ip', 'default')";
        return $this->query($sql);
    }

    public function update($uname, $key, $value) {
        return $this->query("UPDATE users SET `$key`='$value' WHERE username='$uname'");
    }

    public function delete($uname) {
        return $this->query("DELETE from users WHERE username='$uname'");
    }

}
