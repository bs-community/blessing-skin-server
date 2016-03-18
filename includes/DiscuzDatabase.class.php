<?php
/**
 * @Author: printempw
 * @Date:   2016-03-13 14:59:32
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-03-18 16:56:46
 */

class DiscuzDatabase extends AdaptedDatabase
{
    /**
     * Discuz's Fucking dynamic salt
     */
    public function encryptPassword($raw_passwd, $username="") {
        $salt = $this->query("SELECT * FROM ".$this->table_name."
            WHERE ".$this->column_uname."='$username'")->fetch_array()['salt'];
        $encrypt = md5(md5($raw_passwd).$salt);
        return $encrypt;
    }

}
