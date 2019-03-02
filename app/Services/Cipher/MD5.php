<?php

namespace App\Services\Cipher;

class MD5 extends BaseCipher
{
    /**
     * Once MD5 hash.
     */
    public function hash($value, $salt = '')
    {
        return md5($value);
    }
}
