<?php

namespace App\Services\Cipher;

class SHA256 extends BaseCipher
{
    /**
     * Once SHA256 hash.
     */
    public function hash($value, $salt = '')
    {
        return hash('sha256', $value);
    }
}
