<?php

namespace App\Models;

use DB;
use Utils;
use Carbon\Carbon;
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
    protected $token;

    /**
     * Instance of Closet.
     * @var \App\Models\Closet
     */
    protected $closet;

    /**
     * Properties for Eloquent Model.
     */
    public $primaryKey  = 'uid';
    public $timestamps  = false;
    protected $fillable = ['email', 'nickname', 'permission'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'uid' => 'integer',
        'score' => 'integer',
        'avatar' => 'integer',
        'permission' => 'integer',
    ];

    /**
     * Storage size used by user in KiB.
     *
     * @var int
     */
    protected $storageUsed;

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
        if (! $this->closet) {
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
    public function verifyPassword($rawPasswd)
    {
        // Compare directly if any responses is returned by event dispatcher
        if ($result = static::getEncryptedPwdFromEvent($rawPasswd, $this)) {
            return hash_equals($this->password, $result);     // @codeCoverageIgnore
        }

        return app('cipher')->verify($rawPasswd, $this->password, config('secure.salt'));
    }

    /**
     * Try to get encrypted password from event dispatcher.
     *
     * @param  string $rawPasswd
     * @param  User   $user
     * @return mixed
     */
    protected static function getEncryptedPwdFromEvent($rawPasswd, User $user)
    {
        $responses = event(new EncryptUserPassword($rawPasswd, $user));

        return array_get($responses, 0);
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

        // If the email is already registered
        if ($user->uid) return false;

        // Pass the user instance to the callback
        call_user_func($callback, $user);

        // Save once to get uid
        $user->password = '';
        $user->save();

        // Save again with password
        $user->password = static::getEncryptedPwdFromEvent($password, $user) ?: app('cipher')->hash($password, config('secure.salt'));
        $user->save();

        return $user;
    }

    /**
     * Change password of the user.
     *
     * @param  string $new_passwd New password that will be set.
     * @return bool
     */
    public function changePassword($new_passwd)
    {
        $responses = event(new EncryptUserPassword($new_passwd, $this));

        if (isset($responses[0])) {
            $this->password = $responses[0];     // @codeCoverageIgnore
        } else {
            $this->password = app('cipher')->hash($new_passwd, config('secure.salt'));
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
     * @param  string $new_email
     * @return bool
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
        if (! $this->uid) {
            return trans('general.unexistent-user');
        } else {
            return ($this->nickname == "") ? $this->email : $this->nickname;
        }
    }

    /**
     * Set nickname for the user.
     *
     * @param  string $newNickName
     * @return bool
     */
    public function setNickName($newNickName)
    {
        $this->nickname = $newNickName;
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
        if (! $this->token || $refresh) {
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
     * @param int $score
     * @param string $mode What operation should be done, set, plus or minus.
     * @return bool
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
        if (is_null($this->storageUsed)) {
            $this->storageUsed = 0;

            $result = DB::table('textures')
                        ->select(DB::raw("SUM(size) AS total_size"))
                        ->where('uploader', $this->uid)
                        ->first()->total_size;

            $this->storageUsed = $result ?: 0;
        }

        return $this->storageUsed;
    }

    /**
     * Sign for the user, return false if unavailable.
     *
     * @return int|bool
     */
    public function sign()
    {
        if ($this->canSign()) {

            $scoreLimits = explode(',', option('sign_score'));
            $acquiredScore = rand($scoreLimits[0], $scoreLimits[1]);

            $this->setScore($acquiredScore, 'plus');
            $this->last_sign_at = Utils::getTimeFormatted();
            $this->save();

            return $acquiredScore;
        } else {
            return false;
        }
    }

    /**
     * Get remaining time before next signing is available.
     *
     * @return int Time in seconds.
     */
    public function getSignRemainingTime()
    {
        // Convert to timestamp
        $lastSignTime = Carbon::parse($this->getLastSignTime());

        if (option('sign_after_zero')) {
            return Carbon::now()->diffInSeconds(
                (($lastSignTime <= Carbon::today()) ? $lastSignTime : Carbon::tomorrow())
            , false);
        }

        return Carbon::now()->diffInSeconds($lastSignTime->addSeconds(option('sign_gap_time') * 3600), false);
    }

    /**
     * Check if signing in is available now.
     *
     * @return bool
     */
    public function canSign()
    {
        return ($this->getSignRemainingTime() <= 0);
    }

    /**
     * Get the last time of signing in.
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
        // Delete the players he owned
        Player::where('uid', $this->uid)->delete();
        // Delete his closet
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
