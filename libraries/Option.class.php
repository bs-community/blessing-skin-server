<?php
/**
 * @Author: printempw
 * @Date:   2016-03-18 14:02:12
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-04-02 22:50:19
 */

use Database\Database;

class Option
{
    public static function get($key) {
        $db = new Database('options');
        $result = $db->select('option_name', $key);
        return $result['option_value'];
    }

    public static function set($key, $value) {
        $db = new Database('options');
        if (!self::has($key)) {
            self::add($key, $value);
        } else {
            return $db->update('option_value', $value, ['where' => "option_name='$key'"]);
        }
    }

    public static function add($key, $value) {
        $db = new Database('options');
        return $db->insert(['option_name' => $key, 'option_value' => $value]);
    }

    public static function has($key) {
        $db = new Database('options');
        return $db->has('option_name', $key);
    }

    public static function delete($key) {
        $db = new Database('options');
        if (self::has($key)) {
            return $db->delete(['where' => "option_name='$key'"]);
        } else {
            return false;
        }
    }

}
