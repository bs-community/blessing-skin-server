<?php
/**
 * @Author: printempw
 * @Date:   2016-04-03 14:58:11
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-04-03 15:27:11
 */

namespace Encryption;

class SALTED2MD5 implements EncryptInterface
{
    public function encrypt($raw_passwd, $salt = "") {
        $encrypt = md5(md5($raw_passwd).$salt);
        return $encrypt;
    }
}
