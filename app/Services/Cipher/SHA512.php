<?php

namespace App\Services\Cipher;

class SHA512 extends BaseCipher
{
    public function hash($value, $salt = ''): string
    {
        return hash('sha512', $value);
    }
}
