<?php

namespace App\Services\Cipher;

class SALTED2SHA256 extends BaseCipher
{
    public function hash($value, $salt = ''): string
    {
        return hash('sha256', hash('sha256', $value).$salt);
    }
}
