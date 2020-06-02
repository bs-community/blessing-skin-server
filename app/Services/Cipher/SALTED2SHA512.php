<?php

namespace App\Services\Cipher;

class SALTED2SHA512 extends BaseCipher
{
    public function hash($value, $salt = ''): string
    {
        return hash('sha512', hash('sha512', $value).$salt);
    }
}
