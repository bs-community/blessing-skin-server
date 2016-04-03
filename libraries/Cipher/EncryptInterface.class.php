<?php
/**
 * @Author: printempw
 * @Date:   2016-04-03 14:43:46
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-04-03 15:37:11
 */

namespace Encryption;

interface EncryptInterface
{
    /**
     * Encrypt given string, please define it to adapt to other encryption method
     *
     * @param  string $raw_passwd
     * @param  string $salt
     * @return string, ecrypted password
     */
    public function encrypt($raw_passwd, $salt = "");
}
