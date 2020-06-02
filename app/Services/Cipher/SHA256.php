<?php

namespace App\Services\Cipher;

class SHA256 extends BaseCipher
{
    public function hash($value, $salt = ''): string
    {
        return hash('sha256', $value);
    }
}
