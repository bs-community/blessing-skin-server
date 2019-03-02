<?php

namespace App\Services\Cipher;

class SALTED2SHA512 extends BaseCipher
{
    /**
     * SHA512 hash with salt.
     */
    public function hash($value, $salt = '')
    {
        return hash('sha512', hash('sha512', $value).$salt);
    }
}
