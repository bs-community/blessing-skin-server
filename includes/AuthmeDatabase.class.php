<?php
/**
 * @Author: printempw
 * @Date:   2016-03-13 11:59:32
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-03-13 15:29:23
 */

class AuthmeDatabase extends Database implements EncryptInterface, SyncInterface
{
    protected $table_name = "authme";

    /**
     * Default SHA256 encryption method for Authme
     *
     * http://pastebin.com/1wy9g2HT
     */
    public function encryptPassword($raw_passwd, $username="") {
        $hash = hash('sha256', hash('sha256', $raw_passwd) . SALT);
        $encrypt = '$SHA$' . SALT . '$' . $hash;
        return $encrypt;
    }

    public function createRecord($username, $password, $ip) {
        $sql = "INSERT INTO ".$this->table_name." (username, password, ip)
                VALUES ('$username', '$password', '$ip')";
        return $this->query($sql);

    }

    public function sync($username) {
        $exist_in_bs_table = $this->checkRecordExist('username', $username);
        $exist_in_authme_table = ($this->query("SELECT * FROM ".$this->table_name."
            WHERE username='$username'")->num_rows) ? true : false;

        if ($exist_in_bs_table && !$exist_in_authme_table) {
            $result = $this->select('username', $username);
            $this->createRecord($username, $result['password'], $result['ip']);
            return $this->sync($username);
        }

        if (!$exist_in_bs_table && $exist_in_authme_table) {
            $result = $this->query("SELECT * FROM ".$this->table_name."
                WHERE username='$username'")->fetch_array();
            $this->insert(array(
                                "uname" => $username,
                                "passwd" => $result['password'],
                                "ip" => $result['ip']
                            ));
            return $this->sync($username);
        }

        if (!($exist_in_bs_table || $exist_in_authme_table))
            return false;

        if ($exist_in_bs_table && $exist_in_authme_table) {
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
