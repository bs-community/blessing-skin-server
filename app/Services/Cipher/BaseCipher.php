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
        return ($this->hash($password, $salt) === $hash);
    }

}
