<?php

namespace App\Services\Cipher;

class MD5 extends BaseCipher
{
    public function hash($value, $salt = ''): string
    {
        return md5($value);
    }
}
