<?php
/**
 * @Author: printempw
 * @Date:   2016-01-16 23:01:33
 * @Last Modified by:   prpr
 * @Last Modified time: 2016-01-22 15:46:20
 */
$dir = dirname(dirname(__FILE__));
require "$dir/config.php";

class utils {
    private static $connection = null;

    /**
     * Connect to database
     *
     * @return void
     */
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

    /**
     * Use static function to replace raising a exception
     *
     * @param  int $errno
     * @param  string $msg, message to show
     * @return void
     */
    public static function raise($errno = -1, $msg = "Error occured.") {
        $exception['errno'] = $errno;
        $exception['msg'] = $msg;
        die(json_encode($exception));
    }

    /**
     * Return array of rows which matches provided key and value
     *
     * @param  string $key
     * @param  string $value
     * @return array $row, rows matched the key and value
     */
    public static function select($key, $value) {
        $query = self::query("SELECT * FROM users WHERE $key='$value'");
        $row = mysql_fetch_array($query);
        return $row;
    }

    /**
     * Insert a record to database
     *
     * @param  array $array, [uname, passwd, ip]
     * @return bool
     */
    public static function insert($array) {
        $uname = $array['uname'];
        $passwd = $array['passwd'];
        $ip = $array['ip'];
        self::connect();
        $query = self::query("INSERT INTO users (username, password, ip, preference) VALUES ('$uname', '$passwd', '$ip', 'default')");
        return $query;
    }

    public static function update($uname, $key, $value) {
        $query = self::query("UPDATE users SET $key='$value' WHERE username='$uname'");
        return $query;
    }

    public static function delete($uname) {
        $query = self::query("DELETE from users WHERE username='$uname'");
        return $query;
    }

    /**
     * Rename uploaded file
     *
     * @param  array $file, files uploaded via HTTP POST
     * @return string $hash, sha256 hash of file
     */
    public static function upload($file) {
        move_uploaded_file($file["tmp_name"], "./textures/tmp.png");
        $hash = hash_file('sha256', "./textures/tmp.png");
        rename("./textures/tmp.png", "./textures/".$hash);
        return $hash;
    }

    /**
     * Remove a file
     *
     * @param  $filename
     * @return $bool
     */
    public static function remove($filename) {
        if(file_exists($filename)) {
            if (!unlink($filename)) {
                self::raise(-1, "Uncaught error when deleting $filename");
            } else {
                return true;
            }
        }
    }

    /**
     * Simple SQL injection protection
     *
     * @param  string $string
     * @return string
     */
    public static function convertString($string) {
        return stripslashes(trim($string));
    }

    /**
     * Query with raw SQL statement
     *
     * @param  string $sql, raw SQL statement
     * @return bool
     */
    private static function query($sql) {
        self::connect();
        $query = mysql_query($sql, self::$connection);
        if ($query) {
            return $query;
        } else {
            self::raise('1', mysql_error());
        }
        mysql_close(self::$connection);
    }

    /**
     * Get the value of key in an array if index exist
     *
     * @param  string $key
     * @param  array $array
     * @return object
     */
    function getValue($key, $array) {
        if (array_key_exists($key, $array)) {
            return $array[$key];
        }
        return false;
    }

}
?>
