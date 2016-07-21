<?php

namespace App\Services;

use App\Exceptions\E;

/**
 * Light-weight database helper
 *
 * @author  <h@prinzeugen.net>
 */
class Database
{
    /**
     * Instance of MySQLi
     * @var null
     */
    private $connection = null;

    /**
     * Table name to do operations in
     * @var string
     */
    private $table_name = "";

    /**
     * Construct with table name and another config optionally
     *
     * @param string $table_name
     * @param array $config
     */
    function __construct($table_name = '', $config = null) {
        $config = is_null($config) ? (require BASE_DIR.'/config/database.php') : $config;
        $this->connection = self::checkConfig($config);
        $this->table_name = $config['prefix'].$table_name;
    }

    /**
     * Check database config
     *
     * @param  Array  $config
     * @return object instance of MySQLi
     */
    public static function checkConfig(Array $config) {
        // use error control to hide shitty connect warnings
        @$conn = new \mysqli($config['host'], $config['username'], $config['password'], $config['database'], $config['port']);

        if ($conn->connect_error)
            throw new E("Could not connect to MySQL database. Check your config.php:".$conn->connect_error, $conn->connect_errno, true);

        $tables = ['users', 'closets', 'players', 'textures', 'options'];

        foreach ($tables as $table_name) {
            $table_name = $config['prefix'].$table_name;
            $sql = "SELECT table_name FROM `INFORMATION_SCHEMA`.`TABLES`
                    WHERE table_name ='$table_name' AND TABLE_SCHEMA='".$config['database']."'";

            if ($conn->query($sql)->num_rows == 0)
                throw new E("数据库内未发现 $table_name 表。请先运行 <a href='./setup'>安装程序</a>。", -1, true);
        }

        if (!is_dir(BASE_DIR."/textures/"))
            throw new E("根目录下未发现 `textures` 文件夹，请先运行 <a href='./setup'>安装程序</a>，或者手动放置一个。", -1, true);

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
