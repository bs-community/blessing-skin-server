<?php

namespace App\Models;

use DB;
use App;
use Utils;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use App\Events\EncryptUserPassword;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    /**
     * Permissions.
     */
    const BANNED      = -1;
    const NORMAL      = 0;
    const ADMIN       = 1;
    const SUPER_ADMIN = 2;

    /**
     * User Token.
     * @var string
     */
    private $token;

    /**
     * Instance of Closet.
     * @var App\Models\Closet
     */
    private $closet;

    /**
     * Properties for Eloquent Model.
     */
    public $primaryKey  = 'uid';
    public $timestamps  = false;
    protected $fillable = ['email', 'nickname', 'permission'];

    /**
     * Check if user is admin.
     *
     * @return bool
     */
    public function isAdmin()
    {
        return ($this->permission >= static::ADMIN);
    }

    /**
     * Get closet instance.
     *
     * @return App\Models\Closet
     */
    public function getCloset()
    {
        if (!$this->closet) {
            $this->closet = new Closet($this->uid);
        }

        return $this->closet;
    }

    /**
     * Check if given password is correct.
     *
     * @param  string $rawPasswd
     * @return bool
     */
    public function checkPasswd($rawPasswd)
    {
        return (static::encryptPassword($rawPasswd, $this) == $this->password);
    }

    /**
     * Encrypt user's password.
     *
     * @param  string $rawPasswd
     * @param  User   $user
     * @return mixed
     */
    protected static function encryptPassword($rawPasswd, User $user)
    {
        $responses = event(new EncryptUserPassword($rawPasswd, $user));

        return Arr::get($responses, 0,
            // encrypt with current cipher if no response is returned by the event dispatcher
            app('cipher')->encrypt($rawPasswd, config('secure.salt'))
        );
    }

    /**
     * Register a new user.
     *
     * @param  string   $email
     * @param  string   $password
     * @param  \Closure $callback
     * @return User|bool
     */
    public static function register($email, $password, \Closure $callback) {
        $user = static::firstOrNew(['email' => $email]);

        // if the email is already registered
        if ($user->uid)
            return false;

        // save to get uid
        $user->save();

        $user->password = static::encryptPassword($password, $user);

        // pass the user instance to the callback
        call_user_func($callback, $user);

        // save again with password etc.
        $user->save();

        return $user;
    }

    /**
     * Change password of the user.
     *
     * @param  string $new_passwd New password that will be set.
     * @return bool
     */
    public function changePasswd($new_passwd)
    {
        $responses = event(new EncryptUserPassword($new_passwd, $this));

        if (isset($responses[0])) {
            $this->password = $responses[0];
        } else {
            $this->password = app('cipher')->encrypt($new_passwd, config('secure.salt'));
        }

        return $this->save();
    }

    /**
     * Get user permission.
     *
     * @return int
     */
    public function getPermission()
    {
        return $this->permission;
    }

    /**
     * Set user permission.
     *
     * @param  int $permission
     * @return bool
     */
    public function setPermission($permission)
    {
        return $this->update(['permission' => $permission]);
    }

    /**
     * Set new email for user.
     *
     * @param string $new_email
     */
    public function setEmail($new_email)
    {
        $this->email = $new_email;
        return $this->save();
    }

    /**
     * Return Email if nickname is not set.
     *
     * @return string
     */
    public function getNickName()
    {
        if (!$this->uid) {
            return trans('general.unexistent-user');
        } else {
            return ($this->nickname == "") ? $this->email : $this->nickname;
        }
    }

    /**
     * Set nickname for the user.
     *
     * @param  string $new_nickname
     * @return bool
     */
    public function setNickName($new_nickname)
    {
        $this->nickname = $new_nickname;
        return $this->save();
    }

    /**
     * Get user token or generate one.
     *
     * @param  bool  $refresh Refresh token forcely.
     * @return string
     */
    public function getToken($refresh = false)
    {
        if (!$this->token || $refresh) {
            $this->token = md5($this->email . $this->password . config('secure.salt'));
        }

        return $this->token;
    }

    /**
     * Get current score of user.
     *
     * @return int
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * Set user score.
     *
     * @param int    $score
     * @param string $mode  What operation should be done, set, plus or minus.
     */
    public function setScore($score, $mode = "set")
    {
        switch ($mode) {
            case 'set':
                $this->score = $score;
                break;

            case 'plus':
                $this->score += $score;
                break;

            case 'minus':
                $this->score -= $score;
                break;
        }
        return $this->save();
    }

    /**
     * Get the size of storage units used by the user.
     *
     * @return int Size in KiloBytes.
     */
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

    /**
     * Check in for the user, return false if unavailable.
     *
     * @return int|bool
     */
    public function checkIn()
    {
        if ($this->canCheckIn()) {
            $sign_score = explode(',', option('sign_score'));
            $aquired_score = rand($sign_score[0], $sign_score[1]);
            $this->setScore($aquired_score, 'plus');
            $this->last_sign_at = Utils::getTimeFormatted();
            $this->save();
            return $aquired_score;
        } else {
            return false;
        }
    }

    /**
     * Check if checking in is available now.
     *
     * @param  bool  $return_remaining_time Return remaining time.
     * @return int|bool
     */
    public function canCheckIn($return_remaining_time = false)
    {
        // convert to timestamp
        $last_sign_at = strtotime($this->getLastSignTime());

        if (option('sign_after_zero') == "1") {
            $remaining_time = (Carbon::tomorrow()->timestamp - time()) / 3600;
            $can_check_in   = $last_sign_at <= Carbon::today()->timestamp;
        } else {
            $remaining_time = ($last_sign_at + option('sign_gap_time') * 3600 - time()) / 3600;
            $can_check_in   = $remaining_time <= 0;
        }

        return $return_remaining_time ? round($remaining_time) : $can_check_in;
    }

    /**
     * Get the last time of checking in.
     *
     * @return string Formatted time string.
     */
    public function getLastSignTime()
    {
        return $this->last_sign_at;
    }

    /**
     * Get the texture id of user's avatar.
     *
     * @return int
     */
    public function getAvatarId()
    {
        return $this->avatar;
    }

    /**
     * Set user avatar.
     *
     * @param  int $tid
     * @return bool
     */
    public function setAvatar($tid)
    {
        $this->avatar = $tid;
        return $this->save();
    }

    /**
     * Delete the user.
     *
     * @return bool
     */
    public function delete()
    {
        // delete the players he owned
        Player::where('uid', $this->uid)->delete();
        // delete his closet
        DB::table('closets')->where('uid', $this->uid)->delete();

        return parent::delete();
    }

    /**
     * Get the players which are owned by the user.
     *
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function players()
    {
        return $this->hasMany('App\Models\Player', 'uid');
    }

    /**
     * Expand like scope for Eloquent Model.
     */
    public function scopeLike($query, $field, $value)
    {
        return $query->where($field, 'LIKE', "%$value%");
    }

}
