<?php

namespace App\Models;

use Laravel\Passport\HasApiTokens;
use App\Models\Concerns\HasPassword;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;
    use HasPassword;
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
