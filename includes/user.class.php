<?php
/**
 * @Author: printempw
 * @Date:   2016-01-16 23:01:33
 * @Last Modified by:   prpr
 * @Last Modified time: 2016-01-22 14:31:18
 */

class user {
    private $uname = "";
    private $passwd = "";
    private $token = "";
    private $preference = "";

    public $is_registered = false;
    public $is_admin = false;

    function __construct($uname) {
        $this->uname = utils::convertString($uname);
        if (utils::select('username', $this->uname)['uid'] == 1) {
            $this->is_admin = true;
        }
        if (utils::select('username', $this->uname)['password'] != "") {
            $this->passwd = utils::select('username', $this->uname)['password'];
            $this->is_registered = true;
            $this->token = md5($this->uname . $this->passwd.SALT);
            $this->preference = utils::select('username', $this->uname)['preference'];
        }
    }

    public function checkPasswd($raw_passwd) {
        if (md5($raw_passwd) == $this->passwd) {
            return true;
        } else {
            return false;
        }
    }

    public function getToken() {
        return $this->token;
    }

    public function register($passwd, $ip) {
        if (utils::insert(array(
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

    public function getTexture($type) {
        if ($type == "skin") {
            return utils::select('username', $this->uname)['skin_hash'];
        } else if ($type == "cape") {
            return utils::select('username', $this->uname)['cape_hash'];
        }
        return false;
    }

    public function getBinaryTexture($type) {
        $filename = "./textures/".$this->getTexture($type);
        $data = fread(fopen($filename, 'r'), filesize($filename));
        return $data;
    }

    public function setTexture($type, $file) {
        $hash = utils::upload($file);
        if ($type == "skin") {
            // remove the original texture first
            utils::remove("./textures/".$this->getTexture('skin'));
            return utils::update($this->uname, 'skin_hash', $hash);
            echo "shit";
        } else if ($type == "cape") {
            utils::remove("./textures/".$this->getTexture('cape'));
            return utils::update($this->uname, 'cape_hash', $hash);
        }
        return false;
    }

    public function getJsonProfile() {
        if ($this->is_registered) {
            $json['player_name'] = $this->uname;
            if ($this->preference == "slim") {
                $json['model_preference'] = ['slim','default'];
                $json['skins']['slim'] = $this->getTexture('skin');
            } else {
                $json['model_preference'] = ['default'];
                $json['skins']['default'] = $this->getTexture('skin');
            }
            $json['cape'] = $this->getTexture('cape');
        } else {
            $json['errno'] = 1;
            $json['msg'] = "Non-existent user.";
        }
        return json_encode($json);
    }

}
?>
