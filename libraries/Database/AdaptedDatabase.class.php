<?php
/**
 * @Author: printempw
 * @Date:   2016-03-18 16:53:55
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-04-02 22:14:12
 */

namespace Database;

use Database\Database;
use Database\EncryptInterface;
use Database\SyncInterface;
use Option;

class AdaptedDatabase extends Database implements EncryptInterface, SyncInterface
{
    protected $data_table;
    protected $column_uname;
    protected $column_passwd;
    protected $column_ip;

    function __construct($table_name = '') {
        parent::__construct($table_name);
        $this->data_table    = Option::get('data_table_name');
        $this->column_uname  = Option::get('data_column_uname');
        $this->column_passwd = Option::get('data_column_passwd');
        $this->column_ip     = Option::get('data_column_ip');
    }

    public function sync($username, $reverse = false) {
        $exist_in_bs_table   = $this->has('username', $username);
        $exist_in_data_table = $this->has($this->column_uname, $username, $this->data_table);

        if ($exist_in_bs_table && !$exist_in_data_table) {
            $result = $this->select('username', $username);

            $this->insert(array(
                $this->column_uname => $username,
                $this->column_passwd => $result['password'],
                $this->column_ip => $result['ip']
            ), $this->data_table);

            // recursion
            return $this->sync($username);
        }

        if (!$exist_in_bs_table && $exist_in_data_table) {
            $result = $this->select($this->column_uname, $username, null, $this->data_table);

            $this->insert(array(
                "username" => $username,
                "password" => $result[$this->column_passwd],
                "ip"       => $result[$this->column_ip]
            ));

            // recursion
            return $this->sync($username);
        }

        if (!($exist_in_bs_table || $exist_in_data_table))
            // user not exists
            return false;

        if ($exist_in_bs_table && $exist_in_data_table) {
            $passwd1 = $this->select('username', $username)['password'];
            $passwd2 = $this->select($this->column_uname, $username, null, $this->data_table)[$this->column_passwd];

            if ($passwd1 == $passwd2) {
                return true;
            } else {
                // sync password
                if ($reverse) {
                    $this->update($this->column_passwd, $passwd1, ['where' => "$this->column_uname='$username'"], $this->data_table);
                } else {
                    $this->update('password', $passwd2, ['where' => "username='$username'"]);
                }
                return $this->sync($username);
            }
        }

    }
}
