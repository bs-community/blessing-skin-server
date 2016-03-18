<?php
/**
 * @Author: printempw
 * @Date:   2016-03-13 12:15:08
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-03-18 16:56:29
 */

class CrazyDatabase extends AdaptedDatabase
{
    /**
     * Fucking CrazyCrypt1
     *
     * https://github.com/ST-DDT/CrazyLogin/blob/master/php/Encryptors/CrazyCrypt1.php
     */
    public function encryptPassword($raw_passwd, $username="") {
        $text = "ÜÄaeut//&/=I " . $raw_passwd . "7421€547" . $username . "__+IÄIH§%NK " . $raw_passwd;
        $t1 = unpack("H*", $text);
        $t2 = substr($t1[1], 0, mb_strlen($text, 'UTF-8')*2);
        $t3 = pack("H*", $t2);
        $encrypt = hash("sha512", $t3);
        return $encrypt;
    }

}
