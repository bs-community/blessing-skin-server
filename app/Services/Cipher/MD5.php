<?php

namespace App\Services\Cipher;

class MD5 implements EncryptInterface
{
    /**
     * Once MD5 hash
     */
    public function encrypt($value, $salt = "")
    {
        return md5($value);
    }
}
