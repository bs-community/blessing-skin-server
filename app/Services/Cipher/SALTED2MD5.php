<?php

namespace App\Services\Cipher;

class SALTED2MD5 implements EncryptInterface
{
    public function encrypt($raw_passwd, $salt = "") {
        $encrypt = md5(md5($raw_passwd).$salt);
        return $encrypt;
    }
}
