<?php
/**
 * @Author: printempw
 * @Date:   2016-03-13 11:59:32
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-03-18 17:33:27
 */

namespace Database;

use Database\AdaptedDatabase;

class AuthmeDatabase extends AdaptedDatabase
{
    /**
     * Default SHA256 encryption method for Authme
     *
     * http://pastebin.com/1wy9g2HT
     */
    public function encryptPassword($raw_passwd, $username="") {
        $hash = hash('sha256', hash('sha256', $raw_passwd).SALT);
        $encrypt = '$SHA$'.SALT.'$'. $hash;
        return $encrypt;
    }

}
