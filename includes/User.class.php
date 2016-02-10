<?php
/**
 * @Author: printempw
 * @Date:   2016-01-16 23:01:33
 * @Last Modified by:   prpr
 * @Last Modified time: 2016-02-10 21:04:32
 */

class User
{
    private $uname  = "";
    private $passwd = "";
    private $token  = "";

    public $db = null;
    public $is_registered = false;
    public $is_admin = false;

    function __construct($uname) {
        $this->uname = Utils::convertString($uname);
        $this->db = new Database();
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

    public static function checkValidUname($uname) {
        return preg_match("/^([A-Za-z0-9_]+)$/", $uname);
    }

    public static function checkValidPwd($passwd) {
        if (strlen($passwd) > 16 || strlen($passwd) < 5) {
            Utils::raise(1, '无效的密码。密码长度应该大于 6 并小于 15。');
        } else if (Utils::convertString($passwd) != $passwd) {
            Utils::raise(1, '无效的密码。密码中包含了奇怪的字符。');
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
        return $this->db->insert(array(
                                        "uname" => $this->uname,
                                        "passwd" => $passwd,
                                        "ip" => $ip
                                    ));
    }

    public function unRegister() {
        if ($this->getTexture('steve') != "")
            Utils::remove("./textures/".$this->getTexture('steve'));
        if ($this->getTexture('alex') != "")
            Utils::remove("./textures/".$this->getTexture('alex'));
        if ($this->getTexture('cape') != "")
            Utils::remove("./textures/".$this->getTexture('cape'));
        return $this->db->delete($this->uname);
    }

    public function reset() {
        for ($i = 1; $i <= 3; $i++) {
            switch($i) {
                case 1: $type = "steve"; break;
                case 2: $type = "alex"; break;
                case 3: $type = "cape"; break;
            }
            if ($this->getTexture($type) != "")
                Utils::remove("./textures/".$this->getTexture($type));
            $this->db->update($this->uname, 'hash_'.$type, '');
        }
        $this->db->update($this->uname, 'preference', 'default');
    }

    /**
     * Get textures of user
     * @param  string $type steve|alex|cape, 'skin' for texture of preferred model
     * @return string sha256-hash of texture file
     */
    public function getTexture($type) {
        if ($type == "skin")
            $type = ($this->getPreference() == "default") ? "steve" : "alex";
        if ($type == "steve" | $type == "alex" | $type == "cape")
            return $this->db->select('username', $this->uname)['hash_'.$type];
        return false;
    }

    public function getBinaryTexture($type) {
        $filename = "./textures/".$this->getTexture($type);
        if (file_exists($filename)) {
            header('Content-Type: image/png');
            // Cache friendly
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $this->getLastModified()).' GMT');
            header('Content-Length: '.filesize($filename));
            return Utils::fread($filename);
        } else {
            header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
            Utils::raise(-1, 'Texture no longer exists.');
        }
    }

    public function setTexture($type, $file) {
        // Remove the original texture first
        if ($this->getTexture($type) != "")
            Utils::remove("./textures/".$this->getTexture($type));
        $this->updateLastModified();
        $hash = Utils::upload($file);
        if ($type == "steve" | $type == "alex" | $type == "cape")
            return $this->db->update($this->uname, 'hash_'.$type, $hash);
        return false;
    }

    /**
     * Set preferred model
     * @param string $type, 'slim' or 'default'
     */
    public function setPreference($type) {
        return $this->db->update($this->uname, 'preference', $type);
    }

    public function getPreference() {
        return $this->db->select('username', $this->uname)['preference'];
    }

    /**
     * Get JSON profile
     * @param  int $api_type, which API to use, 0 for CustomSkinAPI, 1 for UniSkinAPI
     * @return string, user profile in json format
     */
    public function getJsonProfile($api_type) {
        header('Content-type: application/json');
        if ($this->is_registered) {
            // Support both CustomSkinLoader API & UniSkinAPI
            if ($api_type == 0 || $api_type == 1) {
                $json[($api_type == 0) ? 'username' : 'player_name'] = $this->uname;
                $model = $this->getPreference();
                $sec_model = ($model == 'default') ? 'slim' : 'default';
                if ($api_type == 1) {
                    $json['last_update'] = $this->getLastModified();
                    $json['model_preference'] = [$model, $sec_model];
                }
                // Skins dict order by preference model
                $json['skins'][$model] = $this->getTexture($model == "default" ? "steve" : "alex");
                $json['skins'][$sec_model] = $this->getTexture($sec_model == "default" ? "steve" : "alex");
                $json['cape'] = $this->getTexture('cape');
            } else {
                Utils::raise(-1, '配置文件错误：不支持的 API_TYPE。');
            }
        } else {
            $json['errno'] = 1;
            $json['msg'] = "Non-existent user.";
        }
        return json_encode($json, JSON_PRETTY_PRINT);
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
