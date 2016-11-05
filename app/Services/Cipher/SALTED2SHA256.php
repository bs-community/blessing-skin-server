<?php

namespace App\Services\Cipher;

class SALTED2SHA256 implements EncryptInterface
{
    /**
     * Default SHA256 encryption method for Authme
     *
     * @see http://pastebin.com/1wy9g2HT
     */
    public function encrypt($raw_passwd, $salt = "") {
        $encrypt = hash('sha256', hash('sha256', $raw_passwd).$salt);
        return $encrypt;
    }

}
