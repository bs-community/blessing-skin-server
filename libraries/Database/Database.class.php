<?php
/**
 * @Author: printempw
 * @Date:   2016-02-02 21:59:06
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-06-12 10:43:47
 */

namespace Database;

use Utils;
use E;

class Database implements PasswordInterface, SyncInterface
{
    private $connection = null;

    private $table_name = "";

    function __construct($table_name = '') {
        $this->connection = self::checkConfig();
        $this->table_name = DB_PREFIX.$table_name;
    }

    public static function checkConfig() {
        // use error control to hide shitty connect warnings
        @$conn = new \mysqli(DB_HOST, DB_USER, DB_PASSWD, DB_NAME, DB_PORT);

        if ($conn->connect_error)
            throw new E("无法连接至 MySQL 服务器。请确认 config.php 中的配置是否正确：".$conn->connect_error, $conn->connect_errno, true);

        $sql = "SELECT table_name FROM `INFORMATION_SCHEMA`.`TABLES` WHERE (table_name ='".DB_PREFIX."users'OR table_name ='".DB_PREFIX."options') AND TABLE_SCHEMA='".DB_NAME."'";
        if ($conn->query($sql)->num_rows != 2)
            throw new E("数据库中不存在 ".DB_PREFIX."users 或 ".DB_PREFIX."options 表。请先访问 <a href='./setup'>/setup</a> 进行安装。", -1, true);

        if (!is_dir(BASE_DIR."/textures/"))
            throw new E("textures 文件夹不存在。请先访问 <a href='./setup'>/setup</a> 进行安装，或者手动放置一个。", -1, true);

        $conn->query("SET names 'utf8'");
        return $conn;
    }

    public function query($sql) {
        $result = $this->connection->query($sql);
        if ($this->connection->error)
            throw new E("Database query error: ".$this->connection->error.", Statement: ".$sql, -1);
        return $result;
    }

    public function fetchArray($sql) {
        return $this->query($sql)->fetch_array();
    }

    /**
     * Select records from table
     *
     * @param  string  $key
     * @param  string  $value
     * @param  array   $condition, see function `where`
     * @param  string  $table, which table to operate
     * @param  boolean $dont_fetch_array, return resources if true
     * @return array|resources
     */
    public function select($key, $value, $condition = null, $table = null, $dont_fetch_array = false) {
        $table = is_null($table) ? $this->table_name : $table;

        if (isset($condition['where'])) {
            $sql = "SELECT * FROM $table".$this->where($condition);
        } else {
            $sql = "SELECT * FROM $table WHERE $key='$value'";
        }

        if ($dont_fetch_array) {
            return $this->query($sql);
        } else {
            return $this->fetchArray($sql);
        }

    }

    public function has($key, $value, $table = null) {
        return ($this->getNumRows($key, $value, $table) != 0) ? true : false;
    }

    public function insert($data, $table = null) {
        $keys   = "";
        $values = "";
        $table  = is_null($table) ? $this->table_name : $table;

        foreach($data as $key => $value) {
            if ($value == end($data)) {
                $keys .= '`'.$key.'`';
                $values .= '"'.$value.'"';
            } else {
                $keys .= '`'.$key.'`,';
                $values .= '"'.$value.'", ';
            }
        }

        $sql = "INSERT INTO $table ({$keys}) VALUES ($values)";
        return $this->query($sql);
    }

    public function update($key, $value, $condition = null, $table = null) {
        $table = is_null($table) ? $this->table_name : $table;
        return $this->query("UPDATE $table SET `$key`='$value'".$this->where($condition));
    }

    public function delete($condition = null, $table = null) {
        $table = is_null($table) ? $this->table_name : $table;
        return $this->query("DELETE FROM $table".$this->where($condition));
    }

    public function checkTableExist($table_name) {
        $sql = "SELECT table_name FROM `INFORMATION_SCHEMA`.`TABLES` WHERE (table_name ='$table_name') AND TABLE_SCHEMA='".DB_NAME."'";
        return ($this->query($sql)->num_rows == 0) ? false : true;
    }

    public function checkColumnExist($column_name, $table = null) {
        $table = is_null($table) ? $this->table_name : $table;
        $sql = "SELECT * FROM `INFORMATION_SCHEMA`.`COLUMNS` WHERE TABLE_SCHEMA = '".DB_NAME."' AND TABLE_NAME = '$table' AND COLUMN_NAME = '$column_name'";
        return ($this->query($sql)->num_rows == 0) ? false : true;
    }

    public function getNumRows($key, $value, $table = null) {
        $table = is_null($table) ? $this->table_name : $table;
        $sql = "SELECT * FROM $table WHERE $key='$value'";
        return $this->query($sql)->num_rows;
    }

    public function getRecordNum($table = null) {
        $table = is_null($table) ? $this->table_name : $table;
        $sql = "SELECT * FROM $table WHERE 1";
        return $this->query($sql)->num_rows;
    }

    public function encryptPassword($raw_passwd, $username = "") {
        $class_name = "Cipher\\".\Option::get('encryption');
        return $class_name::encrypt($raw_passwd);
    }

    public function sync($username, $reverse = false) {
        return ($this->has('username', $username)) ? true : false;
    }

    /**
     * Generate where statement
     *
     * @param  array $condition, e.g. array('where'=>'username="shit"', 'limit'=>10, 'order'=>'uid')
     * @return string
     */
    private function where($condition) {
        $statement = "";
        if (isset($condition['where']) && $condition['where'] != "") {
            $statement .= ' WHERE '.$condition['where'];
        }
        if (isset($condition['order'])) {
            $statement .= ' ORDER BY `'.$condition['order'].'`';
        }
        if (isset($condition['limit'])) {
            $statement .= ' LIMIT '.$condition['limit'];
        }
        return $statement;
    }

    function __destruct() {
        if (!is_null($this->connection))
            $this->connection->close();
    }

}
