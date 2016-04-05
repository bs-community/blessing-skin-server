<?php
/**
 * @Author: printempw
 * @Date:   2016-04-03 14:50:45
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-04-05 14:10:07
 */

namespace Cipher;

class SHA256 implements EncryptInterface
{
    /**
     * Default SHA256 encryption method for Authme
     *
     * @see http://pastebin.com/1wy9g2HT
     */
    public static function encrypt($raw_passwd, $salt = "") {
        $encrypt = hash('sha256', hash('sha256', $raw_passwd).$salt);
        return $encrypt;
    }

}
