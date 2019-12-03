<?php

namespace App\Models;

use Event;
use Illuminate\Support\Arr;
use App\Events\GetPlayerJson;
use App\Events\PlayerProfileUpdated;
use Illuminate\Database\Eloquent\Model;

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
        return $this->belongsTo('App\Models\User', 'uid');
    }

    /**
     * Get specific texture of player.
     *
     * @param  string $type "skin" or "cape".
     * @return string The sha256 hash of texture file.
     */
    public function getTexture($type)
    {
        if (in_array($type, self::$types)) {
            return Arr::get(Texture::find($this["tid_$type"]), 'hash');
        }

        return false;
    }

    public function getJsonProfile($api_type)
    {
        // Support both CustomSkinLoader API & UniSkinAPI
        if ($api_type == self::CSL_API || $api_type == self::USM_API) {
            $responses = Event::dispatch(new GetPlayerJson($this, $api_type));

            // If listeners return nothing
            if (isset($responses[0]) && $responses[0] !== null) {
                return $responses[0];     // @codeCoverageIgnore
            } else {
                return $this->generateJsonProfile($api_type);
            }
        } else {
            throw new \InvalidArgumentException('The given api type should be Player::CSL_API or Player::USM_API.');
        }
    }

    public function generateJsonProfile($api_type)
    {
        $json[($api_type == self::CSL_API) ? 'username' : 'player_name'] = $this->name;

        $texture = Texture::find($this->tid_skin);
        $model = empty($texture) ? 'default' : ($texture->type === 'steve' ? 'default' : 'slim');

        if ($api_type == self::USM_API) {
            $json['last_update'] = strtotime($this->last_modified);
            $json['model_preference'] = [$model];
        }

        $skinHash = $this->getTexture('skin');
        if ($model == 'slim') {
            $json['skins']['slim'] = $skinHash;
        }
        $json['skins']['default'] = $skinHash;
        $json['cape'] = $this->getTexture('cape');

        return json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * TODO: This is only for compatibility of 3rd plugins.
     * Remove this in next major version.
     *
     * @deprecated
     */
    public function getPlayerNameAttribute()
    {
        return $this->name;
    }
}
