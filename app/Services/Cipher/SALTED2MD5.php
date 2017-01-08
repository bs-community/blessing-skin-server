<?php

namespace App\Services\Cipher;

class SALTED2MD5 implements EncryptInterface
{
    /**
     * MD5 hash with salt
     */
    public function encrypt($value, $salt = "")
    {
        return md5(md5($value).$salt);
    }
}
