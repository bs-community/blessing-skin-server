<?php

namespace App\Services\Cipher;

class SALTED2SHA256 implements EncryptInterface
{
    /**
     * SHA256 hash with salt
     */
    public function encrypt($value, $salt = "")
    {
        return hash('sha256', hash('sha256', $value).$salt);
    }
}
