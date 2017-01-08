<?php

namespace App\Services\Cipher;

class SHA512 extends BaseCipher
{
    /**
     * Once SHA512 hash
     */
    public function hash($value, $salt = "")
    {
        return hash('sha512', $value);
    }
}
