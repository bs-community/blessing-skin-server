<?php

namespace App\Services\Cipher;

interface EncryptInterface
{
    /**
     * Encrypt given string with given salt.
     *
     * @param  string $value
     * @param  string $salt
     * @return string
     */
    public function hash($value, $salt = "");

    /**
     * Verifies that the given hash matches the given password.
     *
     * @param  string $password
     * @param  string $hash
     * @return bool
     */
    public function verify($password, $hash);
}
