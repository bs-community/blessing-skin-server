<?php

namespace App\Models;

use App\Events\PlayerProfileUpdated;
use App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class Player extends Model
{
    public const CREATED_AT = null;
    public const UPDATED_AT = 'last_modified';

    const CSL_API = 0;
    const USM_API = 1;

    protected static $types = ['skin', 'cape'];

    public $primaryKey = 'pid';
    protected $fillable = ['uid', 'name', 'last_modified'];

    protected $casts = [
        'pid' => 'integer',
        'uid' => 'integer',
        'tid_skin' => 'integer',
        'tid_cape' => 'integer',
    ];

    protected $dispatchesEvents = [
        'retrieved' => \App\Events\PlayerRetrieved::class,
        'updated' => PlayerProfileUpdated::class,
    ];

    public function isBanned()
    {
        return $this->user->permission == User::BANNED;
    }

    public function user()
    {
        return $this->belongsTo(Models\User::class, 'uid');
    }

    public function skin()
    {
        return $this->belongsTo(Models\Texture::class, 'tid_skin');
    }

    public function cape()
    {
        return $this->belongsTo(Models\Texture::class, 'tid_cape');
    }

    public function getModelAttribute()
    {
        return optional($this->skin)->model ?? 'default';
    }

    /**
     * CustomSkinAPI R1.
     */
    public function toJson($options = 0)
    {
        $model = $this->model;
        $profile = [
            'username' => $this->name,
            'skins' => [
                $model => optional($this->skin)->hash,
            ],
            'cape' => optional($this->cape)->hash,
        ];

        return json_encode($profile, $options | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get specific texture of player.
     *
     * @param string $type "skin" or "cape"
     *
     * @return string the sha256 hash of texture file
     */
    public function getTexture($type)
    {
        if (in_array($type, self::$types)) {
            return Arr::get(Texture::find($this["tid_$type"]), 'hash');
        }

        return false;
    }
}
