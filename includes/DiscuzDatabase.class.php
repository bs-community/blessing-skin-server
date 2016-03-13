<?php
/**
 * @Author: printempw
 * @Date:   2016-03-13 14:59:32
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-03-13 15:29:26
 */

class DiscuzDatabase extends Database implements EncryptInterface, SyncInterface
{
    protected $table_name = "pre_ucenter_members";

    /**
     * Discuz's Fucking dynamic salt
     */
    public function encryptPassword($raw_passwd, $username="") {
        $salt = $this->query("SELECT * FROM ".$this->table_name."
            WHERE username='$username'")->fetch_array()['salt'];
        $encrypt = md5(md5($raw_passwd).$salt);
        return $encrypt;
    }

    public function createRecord($username, $password, $ip) {
        $sql = "INSERT INTO ".$this->table_name." (username, password, regip)
                VALUES ('$username', '$password', '$ip')";
        return $this->query($sql);
    }

    public function sync($username) {
        $exist_in_bs_table = $this->checkRecordExist('username', $username);
        $exist_in_discuz_table = ($this->query("SELECT * FROM ".$this->table_name."
            WHERE username='$username'")->num_rows) ? true : false;

        if ($exist_in_bs_table && !$exist_in_discuz_table) {
            $result = $this->select('username', $username);
            $this->createRecord($username, $result['password'], $result['ip']);
            return $this->sync($username);
        }

        if (!$exist_in_bs_table && $exist_in_discuz_table) {
            $result = $this->query("SELECT * FROM ".$this->table_name."
                WHERE username='$username'")->fetch_array();
            $this->insert(array(
                                "uname" => $username,
                                "passwd" => $result['password'],
                                "ip" => $result['regip']
                            ));
            return $this->sync($username);
        }

        if (!($exist_in_bs_table || $exist_in_discuz_table))
            return false;

        if ($exist_in_bs_table && $exist_in_discuz_table) {
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
