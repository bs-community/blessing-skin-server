<?php
/**
 * @Author: printempw
 * @Date:   2016-03-13 12:15:08
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-04-03 15:31:54
 */

namespace Database;

use Database\AdaptedDatabase;

class CrazyDatabase extends AdaptedDatabase
{
    /**
     * Fucking CrazyCrypt1
     *
     * https://github.com/ST-DDT/CrazyLogin/blob/master/php/Encryptors/CrazyCrypt1.php
     */
    public function encryptPassword($raw_passwd, $username="") {
        $class_name = "Encryption\\".\Option::get('encryption');
        return $class_name::encrypt($raw_passwd, $username);
    }

}
