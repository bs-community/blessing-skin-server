<?php
/**
 * @Author: printempw
 * @Date:   2016-01-16 23:01:33
 * @Last Modified by:   prpr
 * @Last Modified time: 2016-01-17 10:09:56
 */
require "./config.php";

class utils {
    private static $connection = null;

    public static function connect() {
        if (!self::$connection) {
            if ($con = mysql_connect(DB_HOST, DB_USER, DB_PASSWD)) {
                self::$connection = $con;
                mysql_select_db(DB_NAME, self::$connection);
            } else {
                $msg = "Can not connect to mysql, check if database info correct in config.php. ".mysql_error();
                self::raise(-1, $msg);
            }
        }
    }

    // use static function to replace raising a exception
    public static function raise($errno = -1, $msg = "Error occured.") {
        $exception['errno'] = $errno;
        $exception['msg'] = $msg;
        die(json_encode($exception));
    }

    public static function select($key, $value) {
        self::connect();
        $query = mysql_query("SELECT * FROM users WHERE $key='$value'", self::$connection);
        $row = mysql_fetch_array($query);
        return $row;
    }

    // @param $array[uname, passwd, ip]
    public static function insert($array) {
        $uname = $array[0];
        $passwd = $array[1];
        $ip = $array[2];
        self::connect();
        $query = mysql_query("INSERT INTO users (username, password, ip) VALUES ('$uname', '$passwd', '$ip')", self::$connection);
        return $query;
    }

    public static function update($uname, $key, $value) {
        self::connect();
        $query = self::query("UPDATE users SET $key='$value' WHERE username='$uname'");
        return $query;
    }

    public static function upload($file) {
        move_uploaded_file($file["tmp_name"], "./textures/tmp.png");
        $hash = hash_file('sha256', "./textures/tmp.png");
        rename("./textures/tmp.png", "./textures/".$hash);
        return $hash;
    }

    public static function convertString($string) {
        return stripslashes(trim($string));
    }

    private static function query($sql) {
        $query = mysql_query($sql, self::$connection);
        if ($query) {
            return $query;
        } else {
            self::raise('1', mysql_error());
        }
    }
}
?>
