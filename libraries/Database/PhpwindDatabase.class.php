<?php
/**
 * @Author: printempw
 * @Date:   2016-06-12 10:46:34
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-06-12 10:48:43
 */

namespace Database;

use Database\AdaptedDatabase;

class PhpwindDatabase extends AdaptedDatabase
{
    /**
     * Same as Discuz
     */
    public function encryptPassword($raw_passwd, $username="") {
        $salt = $this->select($this->column_uname, $username, null, $this->data_table)['salt'];
        $class_name = "Cipher\\".\Option::get('encryption');
        return $class_name::encrypt($raw_passwd, $salt);
    }

}
