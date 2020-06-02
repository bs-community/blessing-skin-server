<?php

namespace App\Services\Cipher;

class PHP_PASSWORD_HASH extends BaseCipher
{
    public function hash($value, $salt = ''): string
    {
        return password_hash($value, PASSWORD_DEFAULT);
    }

    public function verify($password, $hash, $salt = ''): bool
    {
        return password_verify($password, $hash);
    }
}
