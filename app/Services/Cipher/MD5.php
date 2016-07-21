<?php

namespace App\Services\Cipher;

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
