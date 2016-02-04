<?php
/**
 * @Author: printempw
 * @Date:   2016-01-16 23:01:33
 * @Last Modified by:   prpr
 * @Last Modified time: 2016-02-04 10:18:01
 */

class user
{
    private $uname  = "";
    private $passwd = "";
    private $token  = "";

    public $db = null;
    public $is_registered = false;
    public $is_admin = false;

    function __construct($uname) {
        $this->uname = utils::convertString($uname);
        $this->db = new database();
        if ($this->db->checkRecordExist('username', $this->uname)) {
            $this->passwd = $this->db->select('username', $this->uname)['password'];
            $this->token = md5($this->uname . $this->passwd.SALT);
            $this->is_registered = true;
            if ($this->db->select('username', $this->uname)['uid'] == 1) {
                $this->is_admin = true;
            }
        }
    }

    public function checkPasswd($raw_passwd) {
        if (md5($raw_passwd) == $this->passwd) {
            return true;
        } else {
            return false;
        }
    }

    public static function checkValidPwd($passwd) {
        if (strlen($passwd) > 16 || strlen($passwd) < 5) {
            utils::raise(1, 'Illegal password. Password length should be in 5~16.');
        } else if (utils::convertString($passwd) != $passwd) {
            utils::raise(1, 'Illegal password. Password contains unsupported characters.');
        }
        return true;
    }

    public function changePasswd($new_passwd) {
        $this->db->update($this->uname, 'password', md5($new_passwd));
    }

    public function getToken() {
        return $this->token;
    }

    public function register($passwd, $ip) {
        if ($this->db->insert(array(
                                "uname" => $this->uname,
                                "passwd" => $passwd,
                                "ip" => $ip
                            )))
        {
            return true;
        } else {
            return false;
        }
    }

    public function unRegister() {
        if (!is_null($this->getTexture('skin')))
            utils::remove("./textures/".$this->getTexture('skin'));
        if (!is_null($this->getTexture('skin')))
            utils::remove("./textures/".$this->getTexture('cape'));
        return $this->db->delete($this->uname);
    }

    public function getTexture($type) {
        if ($type == "skin") {
            return $this->db->select('username', $this->uname)['skin_hash'];
        } else if ($type == "cape") {
            return $this->db->select('username', $this->uname)['cape_hash'];
        }
        return false;
    }

    public function getBinaryTexture($type) {
        $filename = "./textures/".$this->getTexture($type);
        if (file_exists($filename)) {
            header('Content-Type: image/png');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $this->getLastModified()).' GMT');
            $data = fread(fopen($filename, 'r'), filesize($filename));
            return $data;
        } else {
            utils::raise(-1, 'Texture no longer exists.');
        }
    }

    public function setTexture($type, $file) {
        $hash = utils::upload($file);
        if ($type == "skin") {
            // remove the original texture first
            if ($this->getTexture('skin') != "")
                utils::remove("./textures/".$this->getTexture('skin'));
            $this->updateLastModified();
            return $this->db->update($this->uname, 'skin_hash', $hash);
        } else if ($type == "cape") {
            if ($this->getTexture('cape') != "")
                utils::remove("./textures/".$this->getTexture('cape'));
            $this->updateLastModified();
            return $this->db->update($this->uname, 'cape_hash', $hash);
        }
        return false;
    }

    public function setPreference($type) {
        return $this->db->update($this->uname, 'preference', $type);
    }

    public function getPreference() {
        return $this->db->select('username', $this->uname)['preference'];
    }

    public function getJsonProfile() {
        header('Content-type: application/json');
        if ($this->is_registered) {
            $json['player_name'] = $this->uname;
            $json['last_update'] = $this->getLastModified();
            $preference = $this->getPreference();
            $json['model_preference'] = [$preference];
            $json['skins'][$preference] = $this->getTexture('skin');
            $json['cape'] = $this->getTexture('cape');
        } else {
            $json['errno'] = 1;
            $json['msg'] = "Non-existent user.";
        }
        return json_encode($json);
    }

    public function updateLastModified() {
        // http://stackoverflow.com/questions/2215354/php-date-format-when-inserting-into-datetime-in-mysql
        return $this->db->update($this->uname, 'last_modified', date("Y-m-d H:i:s"));
    }

    /**
     * Get last modified time
     * @return timestamp
     */
    public function getLastModified() {
        return strtotime($this->db->select('username', $this->uname)['last_modified']);
    }

}
