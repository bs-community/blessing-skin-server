<?php
/**
 * @Author: printempw
 * @Date:   2016-03-18 14:02:12
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-03-19 11:25:06
 */

use Database\Database;

class Config
{
    public static function get($key) {
        $conn = Database::checkConfig();
        $sql = "SELECT * FROM ".DB_PREFIX."options WHERE `option_name` = '$key'";
        $result = $conn->query($sql);
        if ($conn->error)
            Utils::raise(-1, "Database query error: ".$conn->error);
        return $result->fetch_array()['option_value'];
    }

    public static function set($key, $value) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWD, DB_NAME, DB_PORT);
        $conn->query("SET names 'utf8'");
        $sql = "UPDATE ".DB_PREFIX."options SET `option_value`='$value' WHERE `option_name`='$key'";
        $result = $conn->query($sql);
        if ($conn->error)
            Utils::raise(-1, "Database query error: ".$conn->error);
        else
            return true;
    }

    public static function setArray($options) {
        foreach ($options as $key => $value) {
            self::set($key, $value);
        }
        return true;
    }
}
