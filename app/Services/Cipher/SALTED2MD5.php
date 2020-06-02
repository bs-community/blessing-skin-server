<?php

namespace App\Services\Cipher;

class SALTED2MD5 extends BaseCipher
{
    public function hash($value, $salt = ''): string
    {
        return md5(md5($value).$salt);
    }
}
