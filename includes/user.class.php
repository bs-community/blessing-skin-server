<?php

class user {
    private $uname = "";
    private $passwd = "";
    private $token = "";

    public $is_registered = false;
    public $is_admin = false;

    function __construct($uname) {
        $this -> $uname = $uname;
        if (utils::select('username', $this -> $uname)['uid'] == 1) {
            $this -> $is_admin = true;
        }
        if (utils::select('username', $this -> $uname)['password'] !== "") {
            $this -> $password = utils::select('username', $this -> $uname)['password'];
            $this -> $is_registered = true;
            $this -> $token = md5($this -> $uname.$this -> $password.SALT);
        }
    }

    public function checkPasswd($raw_passwd) {
        if ($raw_passwd == $this -> $password) {
            return true;
        } else {
            return false;
        }
    }

    public function getToken() {
        return $this -> $token;
    }

    public function register($passwd, $ip) {
        if (utils::insert([$this -> $uname, $passwd, $ip])) {
            return true;
        } else {
            return false;
        }
    }

    public function getTexture($type) {
        if ($type == "skin") {
            return utils::select('username', $this -> $uname)['skin_hash'];
        } else if ($type == "cape") {
            return utils::select('username', $this -> $uname)['cape_hash'];
        }
        return false;
    }

    public function setTexture($type, $file) {
        $hash = utils::upload($file);
        if ($type == "skin") {
            return utils::update($this -> $uname, 'skin_hash', $hash);
        } else if ($type == "cape") {
            return utils::update($this -> $uname, 'cape_hash', $hash);
        }
        return false;
    }

}
?>
