<?php

namespace App\Services\Cipher;

class BCRYPT extends BaseCipher
{
    public function hash($value, $salt = '')
    {
        return password_hash($value, PASSWORD_BCRYPT);
    }

    public function verify($password, $hash, $salt = '')
    {
        return password_verify($password, $hash);
    }
}
