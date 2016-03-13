<?php
/**
 * @Author: printempw
 * @Date:   2016-03-13 12:15:08
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-03-13 15:29:19
 */

class CrazyDatabase extends Database implements EncryptInterface, SyncInterface
{
    protected $table_name = "CrazyLogin_accounts";

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

    public function createRecord($username, $password, $ip) {
        $sql = "INSERT INTO ".$this->table_name." (username, password, ips)
                VALUES ('$username', '$password', '$ip')";
        return $this->query($sql);

    }

    public function sync($username) {
        $exist_in_bs_table = $this->checkRecordExist('username', $username);
        $exist_in_crazy_table = ($this->query("SELECT * FROM ".$this->table_name."
            WHERE username='$username'")->num_rows) ? true : false;

        if ($exist_in_bs_table && !$exist_in_crazy_table) {
            $result = $this->select('username', $username);
            $this->createRecord($username, $result['password'], $result['ip']);
            return $this->sync($username);
        }

        if (!$exist_in_bs_table && $exist_in_crazy_table) {
            $result = $this->query("SELECT * FROM ".$this->table_name."
                WHERE username='$username'")->fetch_array();
            $this->insert(array(
                                "uname" => $username,
                                "passwd" => $result['password'],
                                "ip" => $result['ips']
                            ));
            return $this->sync($username);
        }

        if (!($exist_in_bs_table || $exist_in_crazy_table))
            return false;

        if ($exist_in_bs_table && $exist_in_crazy_table) {
            $passwd1 = $this->select('username', $username)['password'];
            $passwd2 = $this->query("SELECT * FROM ".$this->table_name."
                WHERE username='$username'")->fetch_array()['password'];
            if ($passwd1 == $passwd2) {
                return true;
            } else {
                // sync password
                $this->update($username, 'password', $passwd2);
                return $this->sync($username);
            }
        }

    }

}
