<?php
/**
 * @Author: printempw
 * @Date:   2016-03-18 14:02:12
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-03-27 11:28:24
 */

use Database\Database;

class Option
{
    public static function get($key) {
        $conn = Database::checkConfig();
        $sql = "SELECT * FROM ".DB_PREFIX."options WHERE `option_name` = '$key'";
        $result = $conn->query($sql);
        if ($conn->error)
            throw new E("Database query error: ".$conn->error, -1);
        return $result->fetch_array()['option_value'];
    }

    public static function set($key, $value) {
        $conn = Database::checkConfig();
        if (!self::has($key)) {
            self::add($key, $value);
        } else {
            $sql = "UPDATE ".DB_PREFIX."options SET `option_value`='$value' WHERE `option_name`='$key'";
            $result = $conn->query($sql);
            if ($conn->error)
                throw new E("Database query error: ".$conn->error, -1);
            else
                return true;
        }
    }

    public static function add($key, $value) {
        $conn = Database::checkConfig();
        // check if option exists
        if (!self::has($key)) {
            $sql = "INSERT INTO ".DB_PREFIX."options (`option_name`, `option_value`) VALUES ('$key', '$value')";
            $result = $conn->query($sql);
            if ($conn->error)
                throw new E("Database query error: ".$conn->error, -1);
            else
                return true;
        } else {
            return true;
        }
    }

    public static function has($key) {
        $conn = Database::checkConfig();
        // check if option exists
        $sql = "SELECT * FROM ".DB_PREFIX."options WHERE `option_name` = '$key'";
        if ($conn->query($sql)->num_rows != 0) {
            return true;
        } else {
            return false;
        }
    }

    public static function delete($key) {
        $conn = Database::checkConfig();
        if (self::has($key)) {
            $sql = "DELETE FROM ".DB_PREFIX."options WHERE `option_name`='$key'";
            $result = $conn->query($sql);
            if ($conn->error)
                throw new E("Database query error: ".$conn->error, -1);
            else
                return true;
        } else {
            return false;
        }
    }

    public static function setArray($options) {
        foreach ($options as $key => $value) {
            self::set($key, $value);
        }
        return true;
    }
}
