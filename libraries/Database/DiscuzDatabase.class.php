<?php
/**
 * @Author: printempw
 * @Date:   2016-03-13 14:59:32
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-04-03 15:41:40
 */

namespace Database;

use Database\AdaptedDatabase;

class DiscuzDatabase extends AdaptedDatabase
{
    /**
     * Parse Discuz's Fucking dynamic salt
     */
    public function encryptPassword($raw_passwd, $username="") {
        $salt = $this->select($this->column_uname, $username, null, $this->data_table)['salt'];
        $class_name = "Cipher\\".\Option::get('encryption');
        return $class_name::encrypt($raw_passwd, $salt);
    }

}
