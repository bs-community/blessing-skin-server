<?php
/**
 * @Author: printempw
 * @Date:   2016-03-13 11:53:47
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-04-03 15:36:11
 */

namespace Database;

interface PasswordInterface
{
    /**
     * Return encrypted password
     *
     * @param  string $raw_passwd
     * @param  string $username
     * @return string, ecrypted password
     */
    public function encryptPassword($raw_passwd, $username="");

}
