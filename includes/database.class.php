<?php
/**
 * @Author: printempw
 * @Date:   2016-02-02 21:59:06
 * @Last Modified by:   prpr
 * @Last Modified time: 2016-02-02 23:48:47
 */

class database
{
    private $connection = null;

    function __construct() {
        $this->connection = self::checkConfig();
    }

    public static function checkConfig() {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWD, DB_NAME);
        if ($conn->connect_error) {
            utils::raise(-1, "Can not connect to mysql, check if database info correct in config.php. ".
                                $conn->connect_error);
        }
        return $conn;
    }

    public function query($sql) {
        $result = $this->connection->query($sql);
        if (!$this->connection->error) {
            return $result;
        }
        utils::raise(-1, "Database query error: ", $this->connection->error);
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
        return $this->query("UPDATE users SET $key='$value' WHERE username='$uname'");
    }

    public function delete($uname) {
        return $this->query("DELETE from users WHERE username='$uname'");
    }

}
