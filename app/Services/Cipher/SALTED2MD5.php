<?php

namespace App\Services\Cipher;

class SALTED2MD5 extends BaseCipher
{
    /**
     * MD5 hash with salt
     */
    public function hash($value, $salt = "")
    {
        return md5(md5($value).$salt);
    }
}
