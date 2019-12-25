<?php

namespace App\Services\Cipher;

class ARGON2I extends BaseCipher
{
    public function hash($value, $salt = '')
    {
        return password_hash($value, PASSWORD_ARGON2I);
    }

    public function verify($password, $hash, $salt = '')
    {
        return password_verify($password, $hash);
    }
}
