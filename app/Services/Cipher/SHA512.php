<?php

namespace App\Services\Cipher;

class SHA512 implements EncryptInterface
{
    /**
     * Once SHA512 hash
     */
    public function encrypt($value, $salt = "")
    {
        return hash('sha512', $value);
    }
}
