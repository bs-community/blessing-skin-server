<?php
/**
 * @Author: printempw
 * @Date:   2016-03-13 11:59:32
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-04-03 15:41:39
 */

namespace Database;

use Database\AdaptedDatabase;

class AuthmeDatabase extends AdaptedDatabase
{

    /**
     * Default SHA256 encryption method for Authme
     *
     * @see http://pastebin.com/1wy9g2HT
     */
    public function encryptPassword($raw_passwd, $username="") {
        if ($this->has('username', $username)) {
            $salt = $this->getPwdInfo($username)['salt'];
        } else {
            // generate random salt
            $salt = \Utils::generateRndString(16);
        }
        $class_name = "Cipher\\".\Option::get('encryption');
        $encrypt = '$SHA$'.$salt.'$'. $class_name::encrypt($raw_passwd, $salt);
        return $encrypt;
    }

    /**
     * Parse fucking inline salt
     *
     * @see    https://github.com/Xephi/AuthMeReloaded/blob/master/samples/website_integration/sha256/integration.php
     * @param  string $username
     * @return array
     */
    private function getPwdInfo($username) {
        $hashed = $this->select($this->column_uname, $username)['password'];
        $parts = explode('$', $hashed);
        $pwd_info['password'] = $parts[3];
        $pwd_info['salt'] = $parts[2];
        return $pwd_info;
    }

}
