<?php
/**
 * @Author: printempw
 * @Date:   2016-04-03 14:53:42
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-04-03 15:19:09
 */

namespace Encryption;

class MD5 implements EncryptInterface
{
    /**
     * Once MD5 encrypt
     */
    public function encrypt($raw_passwd, $salt = "") {
        $encrypt = md5($raw_passwd);
        return $encrypt;
    }
}
