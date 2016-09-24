<?php

namespace App\Models;

use Option;
use Utils;

class User
{
    public  $uid            = "";
    public  $email          = "";

    private $password       = "";
    private $token          = "";

    private $storage_used   = null;

    /**
     * Instance of App\Services\Cipher\{cipher}
     * @var null
     */
    private $cipher         = null;

    /**
     * Instance of App\Models\UserModel
     * @var null
     */
    private $model          = null;

    /**
     * Instance of App\Models\Closet
     * @var null
     */
    public $closet          = null;

    public $is_registered   = false;
    public $is_admin        = false;

    /**
     * Pass uid or an array to instantiate a user
     *
     * $info = [
     *   'username' => 'foo',
     *   'email'    => 'foo@bar.com'
     * ];
     *
     * @param int   $uid
     * @param array $info
     */
    public function __construct($uid, Array $info = [])
    {
        // Construct user with uid|email|player_name
        if ($uid !== null) {
            $this->uid          = $uid;
            $this->model        = UserModel::find($uid);
        } else {
            if (isset($info['email'])) {
                $this->email    = Utils::convertString($info['email']);
                $this->model    = UserModel::where('email', $this->email)->first();
            } elseif (isset($info['username'])) {
                $player         = PlayerModel::where('player_name', $info['username'])->first();
                $this->uid      = $player ? $player['uid'] : 0;
                $this->model    = UserModel::find($this->uid);
            } else {
                throw new \InvalidArgumentException('Invalid arguments');
            }
        }

        $class_name              = "App\Services\Cipher\\".config('secure.cipher');
        $this->cipher            = new $class_name;

        if (!is_null($this->model)) {
            $this->is_registered = true;
            $this->uid           = $this->model->uid;
            $this->email         = $this->model->email;
            $this->password      = $this->model->password;
            $this->token         = md5($this->email . $this->password . config('secure.salt'));
            $this->closet        = new Closet($this->uid);
            $this->is_admin      = $this->model->permission == 1 || $this->model->permission == 2;
        }
    }

    public function checkPasswd($raw_passwd)
    {
        return ($this->cipher->encrypt($raw_passwd, config('secure.salt')) == $this->password);
    }

    public function changePasswd($new_passwd)
    {
        $this->model->password = $this->cipher->encrypt($new_passwd, config('secure.salt'));
        return $this->model->save();
    }

    public function getPermission()
    {
        return $this->model->permission;
    }

    /**
     * Set user permission
     * @param int $permission
     * -1 - banned
     *  0 - normal
     *  1 - admin
     *  2 - super admin
     */
    public function setPermission($permission)
    {
        return $this->model->update(['permission' => $permission]);
    }

    public function setEmail($new_email)
    {
        $this->model->email = $new_email;
        return $this->model->save();
    }

    public function getNickName()
    {
        if (!$this->is_registered) {
            return trans('general.unexistent-user');
        } else {
            return ($this->model->nickname == "") ? $this->email : $this->model->nickname;
        }
    }

    public function setNickName($new_nickname)
    {
        $this->model->nickname = $new_nickname;
        return $this->model->save();
    }

    public function getToken($refresh = false)
    {
        if ($this->is_registered && ($this->token === "" || $refresh)) {
            $this->token = md5($this->model->email . $this->model->password . config('secure.salt'));
        }

        return $this->token;
    }

    public function getScore()
    {
        return $this->model->score;
    }

    public function setScore($score, $mode = "set")
    {
        switch ($mode) {
            case 'set':
                $this->model->score = $score;
                break;

            case 'plus':
                $this->model->score += $score;
                break;

            case 'minus':
                $this->model->score -= $score;
                break;
        }
        return $this->model->save();
    }

    public function getStorageUsed()
    {
        if (is_null($this->storage_used)) {
            $this->storage_used = 0;
            // recalculate
            $sql = "SELECT SUM(`size`) AS total_size FROM `{table}` WHERE uploader = {$this->uid}";
            $result = \Database::table('textures')->fetchArray($sql)['total_size'];
            $this->storage_used = $result ?: 0;
        }
        return $this->storage_used;
    }

    public function checkIn()
    {
        if ($this->canCheckIn()) {
            $sign_score = explode(',', Option::get('sign_score'));
            $aquired_score = rand($sign_score[0], $sign_score[1]);
            $this->setScore($aquired_score, 'plus');
            $this->model->last_sign_at = Utils::getTimeFormatted();
            $this->model->save();
            return $aquired_score;
        } else {
            return false;
        }
    }

    public function canCheckIn($return_remaining_time = false)
    {
        // convert to timestamp
        $last_sign_timestamp     = strtotime($this->getLastSignTime());
        $zero_timestamp_today    = strtotime(date('Y-m-d',time()));
        $zero_timestamp_tomorrow = strtotime(date('Y-m-d',strtotime('+1 day')));

        if (Option::get('sign_after_zero')) {
            $remaining_time = ($zero_timestamp_tomorrow - $last_sign_timestamp) / 3600;
            return $return_remaining_time ? round($remaining_time) : ($last_sign_timestamp <= $zero_timestamp_today);
        } else {
            $remaining_time = ($last_sign_timestamp + Option::get('sign_gap_time') * 3600 - time()) / 3600;
            return $return_remaining_time ? round($remaining_time) : ($remaining_time <= 0);
        }
    }

    public function getLastSignTime()
    {
        return $this->model->last_sign_at;
    }

    /**
     * Register a new user
     * @param  string $password
     * @param  string $ip
     * @return object, instance of App\Models\User
     */
    public function register($password, $ip)
    {
        $user = new UserModel();

        $user->email        = $this->email;
        $user->password     = $this->cipher->encrypt($password, config('secure.salt'));
        $user->ip           = $ip;
        $user->score        = Option::get('user_initial_score');
        $user->register_at  = Utils::getTimeFormatted();
        $user->last_sign_at = Utils::getTimeFormatted(time() - 86400);
        $user->permission   = 0;
        $user->save();

        $closet             = new ClosetModel();
        $closet->uid        = $user->uid;
        $closet->textures   = "";
        $closet->save();

        $this->model         = $user;
        $this->uid           = $user->uid;
        $this->is_registered = true;

        return $this;
    }

    public function getPlayers()
    {
        return PlayerModel::where('uid', $this->uid)->get();
    }

    public function getAvatarId()
    {
        return $this->model->avatar;
    }

    public function setAvatar($tid)
    {
        $this->model->avatar = $tid;
        return $this->model->save();
    }

    public function delete()
    {
        PlayerModel::where('uid', $this->uid)->delete();
        ClosetModel::where('uid', $this->uid)->delete();
        return $this->model->delete();
    }

}

class UserModel extends \Illuminate\Database\Eloquent\Model
{
    public $primaryKey = 'uid';
    protected $table = 'users';
    public $timestamps = false;

    protected $fillable = ['email', 'nickname', 'permission'];

    public function scopeLike($query, $field, $value)
    {
        return $query->where($field, 'LIKE', "%$value%");
    }
}
