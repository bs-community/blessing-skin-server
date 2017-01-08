<?php

namespace App\Services\Cipher;

interface EncryptInterface
{
    /**
     * Encrypt given string w/ or w/o salt
     *
     * @param  string $value
     * @param  string $salt
     * @return string
     */
    public function encrypt($value, $salt = "");
}
