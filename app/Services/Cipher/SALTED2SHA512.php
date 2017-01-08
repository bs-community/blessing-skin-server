<?php

namespace App\Services\Cipher;

class SALTED2SHA512 implements EncryptInterface
{
    /**
     * SHA512 with salt
     */
    public function encrypt($value, $salt = "")
    {
        return hash('sha512', hash('sha256', $value).$salt);
    }

}
