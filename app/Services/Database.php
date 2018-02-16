<?php

namespace App\Services;

use Exception;
use InvalidArgumentException;

/**
 * Light-weight database helper
 *
 * @author <h@prinzeugen.net>
 */
class Database
{
    /**
     * Instance of MySQLi.
     *
     * @var null
     */
    private $connection = null;

    /**
     * Database name.
     *
     * @var array
     */
    private $database   = "";

    /**
     * Name of table to do operations in.
     *
     * @var string
     */
    private $tableName = "";

    /**
     * Construct with a config array.
     *
     * @param array $config
     */
    public function __construct($config = null)
    {
        try {
            $this->connection = self::prepareConnection($config);
        } catch (Exception $e) {
            // throw with message
            throw new InvalidArgumentException("Could not connect to MySQL database. ".
                $e->getMessage(), $e->getCode());
        }

        $this->database = array_get($config, 'database', config('database.connections.mysql.database'));
        $this->connection->query("SET names 'utf8'");
    }

    /**
     * Try to connect to the database with given config.
     *
     * @param  array $config
     * @return \mysqli
     *
     * @throws InvalidArgumentException
     */
    public static function prepareConnection($config = null)
    {
        $config = $config ?: config('database.connections.mysql');

        // use error control operator to hide warnings
        @$conn = new \mysqli(
            $config['host'],
            $config['username'],
            $config['password'],
            $config['database'],
            $config['port']
        );

        if ($conn->connect_error) {
            throw new InvalidArgumentException($conn->connect_error, $conn->connect_errno);
        }

        return $conn;
    }

    public function table($tableName, $no_prefix = false)
    {
        if ($this->connection->real_escape_string($tableName) == $tableName) {

            $this->tableName = $no_prefix ? "{$this->database}.$tableName" : config('database.connections.mysql.prefix').$tableName;
            return $this;

        } else {
            throw new InvalidArgumentException('Table name contains invalid characters', 1);
        }
    }

    public function query($sql)
    {
        // compile patterns
        $sql = str_replace('{table}', $this->tableName, $sql);

        $result = $this->connection->query($sql);

        if ($this->connection->error)
            throw new Exception("Database query error: ".$this->connection->error.", Statement: ".$sql, -1);

        return $result;
    }

    public function fetchArray($sql)
    {
        return $this->query($sql)->fetch_array();
    }

    /**
     * Select records from table
     *
     * @param  string  $key
     * @param  string  $value
     * @param  array   $condition        See function `where`
     * @param  string  $table            Which table to operate
     * @param  bool    $dont_fetch_array Return resources if true
     * @return array|resources
     */
    public function select($key, $value, $condition = null, $table = null, $dont_fetch_array = false)
    {
        $table = $table ?: $this->tableName;

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

    public function insert($data, $table = null)
    {
        $keys   = "";
        $values = "";
        $table  = $table ?: $this->tableName;

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

    public function has($key, $value, $table = null)
    {
        return ($this->getNumRows($key, $value, $table) != 0) ? true : false;
    }

    public function hasTable($tableName)
    {
        $sql = "SELECT table_name FROM `INFORMATION_SCHEMA`.`TABLES` WHERE (table_name = '$tableName') AND TABLE_SCHEMA='{$this->database}'";

        return ($this->query($sql)->num_rows != 0) ? true : false;
    }

    public function update($key, $value, $condition = null, $table = null)
    {
        $table = $table ?: $this->tableName;

        return $this->query("UPDATE $table SET `$key`='$value'".$this->where($condition));
    }

    public function delete($condition = null, $table = null)
    {
        $table = $table ?: $this->tableName;

        return $this->query("DELETE FROM $table".$this->where($condition));
    }

    public function getNumRows($key, $value, $table = null)
    {
        $table = $table ?: $this->tableName;

        $sql = "SELECT * FROM $table WHERE $key='$value'";
        return $this->query($sql)->num_rows;
    }

    public function getRecordNum($table = null)
    {
        $table = $table ?: $this->tableName;

        $sql = "SELECT * FROM $table WHERE 1";
        return $this->query($sql)->num_rows;
    }

    /**
     * Generate where statement
     *
     * @param  array $condition e.g. array('where'=>'username="shit"', 'limit'=>10, 'order'=>'uid')
     * @return string
     */
    private function where($condition)
    {
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

    public function __destruct()
    {
        if (! is_null($this->connection)) {
            $this->connection->close();
        }
    }

}
