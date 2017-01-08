<?php

namespace App\Services\Cipher;

class SHA256 implements EncryptInterface
{
    /**
     * Once SHA256 hash
     */
    public function encrypt($value, $salt = "")
    {
        return hash('sha256', $value);
    }
}
