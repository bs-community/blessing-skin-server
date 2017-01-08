<?php

namespace App\Services\Cipher;

abstract class BaseCipher implements EncryptInterface
{
    /**
     * {@inheritdoc}
     */
    public function hash($value, $salt = "")
    {
        //
    }

    /**
     * {@inheritdoc}
     */
    public function verify($password, $hash, $salt = "")
    {
        return hash_equals($hash, $this->hash($password, $salt));
    }

}
