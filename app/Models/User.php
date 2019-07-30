<?php

namespace App\Models;

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

    const BANNED = -1;
    const NORMAL = 0;
    const ADMIN = 1;
    const SUPER_ADMIN = 2;

    public $primaryKey = 'uid';
    public $timestamps = false;
    protected $fillable = ['email', 'nickname', 'permission'];

    protected $casts = [
        'uid' => 'integer',
        'score' => 'integer',
        'avatar' => 'integer',
        'permission' => 'integer',
        'verified' => 'bool',
    ];

    protected $hidden = ['password', 'remember_token'];

    public function isAdmin()
    {
        return $this->permission >= static::ADMIN;
    }

    public function closet()
    {
        return $this->belongsToMany(Texture::class, 'user_closet')->withPivot('item_name');
    }

    public function getPlayerNameAttribute()
    {
        $player = $this->players->first();

        return $player ? $player->name : '';
    }

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
        $this->password = Arr::get($responses, 0, app('cipher')->hash($password, config('secure.salt')));
        return $this->save();
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
