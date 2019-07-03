<?php

namespace App\Models;

use DB;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Laravel\Passport\HasApiTokens;
use App\Events\EncryptUserPassword;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;
    use HasApiTokens;

    /**
     * Permissions.
     */
    const BANNED = -1;
    const NORMAL = 0;
    const ADMIN = 1;
    const SUPER_ADMIN = 2;

    /**
     * Properties for Eloquent Model.
     */
    public $primaryKey = 'uid';
    public $timestamps = false;
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
        'verified' => 'bool',
    ];

    protected $hidden = ['password', 'remember_token'];

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
        return $this->permission >= static::ADMIN;
    }

    public function closet()
    {
        return $this->belongsToMany(Texture::class, 'user_closet')->withPivot('item_name');
    }

    /**
     * Retrieve the player name of first player.
     */
    public function getPlayerNameAttribute()
    {
        $player = $this->players->first();

        return $player ? $player->name : '';
    }

    /**
     * Update the player name of first player.
     */
    public function setPlayerNameAttribute($value)
    {
        $player = $this->players->first();
        if ($player) {
            $player->name = $value;
            $player->save();
        }
    }

    public function verifyPassword($raw)
    {
        // Compare directly if any responses is returned by event dispatcher
        if ($result = static::getEncryptedPwdFromEvent($raw, $this)) {
            return hash_equals($this->password, $result);     // @codeCoverageIgnore
        }

        return app('cipher')->verify($raw, $this->password, config('secure.salt'));
    }

    /**
     * Try to get encrypted password from event dispatcher.
     *
     * @param  string $raw
     * @param  User   $user
     * @return mixed
     */
    public static function getEncryptedPwdFromEvent($raw, User $user)
    {
        $responses = event(new EncryptUserPassword($raw, $user));

        return Arr::get($responses, 0);
    }

    /**
     * Change password of the user.
     *
     * @param  string $password New password that will be set.
     * @return bool
     */
    public function changePassword($password)
    {
        $responses = event(new EncryptUserPassword($password, $this));

        if (isset($responses[0])) {
            $this->password = $responses[0];     // @codeCoverageIgnore
        } else {
            $this->password = app('cipher')->hash($password, config('secure.salt'));
        }

        return $this->save();
    }

    /**
     * Set user score.
     *
     * @param int $score
     * @param string $mode What operation should be done, set, plus or minus.
     * @return bool
     */
    public function setScore($score, $mode = 'set')
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
                        ->select(DB::raw('SUM(size) AS total_size'))
                        ->where('uploader', $this->uid)
                        ->first()->total_size;

            $this->storageUsed = $result ?: 0;
        }

        return (int) $this->storageUsed;
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
            $this->last_sign_at = get_datetime_string();
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
        $lastSignTime = Carbon::parse($this->last_sign_at);

        if (option('sign_after_zero')) {
            return Carbon::now()->diffInSeconds(
                $lastSignTime <= Carbon::today() ? $lastSignTime : Carbon::tomorrow(),
                false
            );
        }

        return Carbon::now()->diffInSeconds($lastSignTime->addHours(option('sign_gap_time')), false);
    }

    public function canSign()
    {
        return $this->getSignRemainingTime() <= 0;
    }

    public function delete()
    {
        Player::where('uid', $this->uid)->delete();

        return parent::delete();
    }

    public function players()
    {
        return $this->hasMany('App\Models\Player', 'uid');
    }

    public function getAuthIdentifier()
    {
        return $this->uid;
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
