<?php

namespace App\Services\Cipher;

interface EncryptInterface
{
    /**
     * Encrypt given string w/ or w/o salt
     *
     * @param  string $raw_passwd
     * @param  string $salt
     * @return string ecrypted password
     */
    public function encrypt($raw_passwd, $salt = "");
}
