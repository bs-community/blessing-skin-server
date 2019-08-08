<?php

namespace App\Models;

use Illuminate\Support\Arr;
use Laravel\Passport\HasApiTokens;
use App\Models\Concerns\HasPassword;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
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

    protected $primaryKey = 'uid';
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

    protected static $mappings = [];

    protected static function boot()
    {
        parent::boot();

        $columns = [
            'uid',
            'email',
            'nickname',
            'score',
            'avatar',
            'password',
            'ip',
            'permission',
            'last_sign_at',
            'register_at',
            'verified',
        ];
        array_walk($columns, function ($column) {
            static::$mappings[$column] = Arr::get(static::$mappings, $column, $column);
        });

        static::addGlobalScope(function (Builder $builder) {
            $query = $builder->getQuery();

            $mapItem = function ($item) {
                if (Arr::has(static::$mappings, $item['column'])) {
                    $item['column'] = static::$mappings[$item['column']];
                }
                return $item;
            };
            $mapColumn = function ($column) {
                return Arr::get(static::$mappings, $column, $column);
            };

            $query->wheres = array_map($mapItem, $query->wheres);
            if ($query->columns) {
                $query->columns = array_map($mapColumn, $query->columns);
            }
            if ($query->orders) {
                $query->orders = array_map($mapItem, $query->orders);
            }
            if ($query->groups) {
                $query->groups = array_map($mapColumn, $query->groups);
            }
            if ($query->havings) {
                $query->havings = array_map($mapItem, $query->havings);
            }

            $builder->setQuery($query);
            return $builder;
        });
    }

    public function isAdmin()
    {
        return $this->permission >= static::ADMIN;
    }

    public function closet()
    {
        return $this->belongsToMany(Texture::class, 'user_closet')->withPivot('item_name');
    }

    public function getUidAttribute()
    {
        return intval($this->attributes[$this->primaryKey]);
    }

    public function getEmailAttribute()
    {
        return $this->attributes[static::$mappings['email']];
    }

    public function setEmailAttribute($value)
    {
        $this->attributes[static::$mappings['email']] = $value;
    }

    public function getNicknameAttribute()
    {
        return $this->attributes[static::$mappings['nickname']];
    }

    public function setNicknameAttribute($value)
    {
        $this->attributes[static::$mappings['nickname']] = $value;
    }

    public function getScoreAttribute()
    {
        return intval($this->attributes[static::$mappings['score']]);
    }

    public function setScoreAttribute($value)
    {
        $this->attributes[static::$mappings['score']] = $value;
    }

    public function getAvatarAttribute()
    {
        return intval($this->attributes[static::$mappings['avatar']]);
    }

    public function setAvatarAttribute($value)
    {
        $this->attributes[static::$mappings['avatar']] = $value;
    }

    public function getPasswordAttribute()
    {
        return $this->attributes[static::$mappings['password']];
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes[static::$mappings['password']] = $value;
    }

    public function getIpAttribute()
    {
        return $this->attributes[static::$mappings['ip']];
    }

    public function setIpAttribute($value)
    {
        $this->attributes[static::$mappings['ip']] = $value;
    }

    public function getPermissionAttribute()
    {
        return intval($this->attributes[static::$mappings['permission']]);
    }

    public function setPermissionAttribute($value)
    {
        $this->attributes[static::$mappings['permission']] = $value;
    }

    public function getLastSignAtAttribute()
    {
        return $this->attributes[static::$mappings['last_sign_at']];
    }

    public function setLastSignAtAttribute($value)
    {
        $this->attributes[static::$mappings['last_sign_at']] = $value;
    }

    public function getRegisterAtAttribute()
    {
        return $this->attributes[static::$mappings['register_at']];
    }

    public function setRegisterAtAttribute($value)
    {
        $this->attributes[static::$mappings['register_at']] = $value;
    }

    public function getVerifiedAttribute()
    {
        return boolval($this->attributes[static::$mappings['verified']]);
    }

    public function setVerifiedAttribute($value)
    {
        $this->attributes[static::$mappings['verified']] = $value;
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
