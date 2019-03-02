<?php

namespace App\Models;

use Event;
use Response;
use App\Models\User;
use App\Events\GetPlayerJson;
use App\Events\PlayerProfileUpdated;
use App\Exceptions\PrettyPageException;
use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    /**
     * Json APIs.
     */
    const CSL_API = 0;
    const USM_API = 1;

    protected static $types = ['skin', 'cape'];

    /**
     * Properties for Eloquent Model.
     */
    public    $primaryKey = 'pid';
    public    $timestamps = false;
    protected $fillable   = ['uid', 'player_name', 'last_modified'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'pid' => 'integer',
        'uid' => 'integer',
        'tid_skin' => 'integer',
        'tid_cape' => 'integer',
    ];

    /**
     * Check if the player is banned.
     *
     * @return bool
     */
    public function isBanned()
    {
        return $this->user->getPermission() == User::BANNED;
    }

    /**
     * Return the owner of the player.
     *
     * @return \App\Models\User
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'uid');
    }

    public function getTidSkinAttribute($value)
    {
        if ($value == -1) {
            $this->tid_skin = $value = $this->preference == 'default'
                ? $this->tid_steve
                : $this->tid_alex;
            $this->save();
        }

        return $value;
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
            return Texture::find($this["tid_$type"])['hash'];
        }

        return false;
    }

    /**
     * Set textures for the player.
     *
     * @param  array $tids
     * @return $this
     */
    public function setTexture(Array $tids)
    {
        foreach (self::$types as $type) {
            $property = "tid_$type";

            if (isset($tids[$property])) {
                $this->$property = $tids[$property];
            }
        }

        $this->last_modified = get_datetime_string();

        $this->save();

        event(new PlayerProfileUpdated($this));

        return $this;
    }

    /**
     * Check and delete invalid textures from player profile.
     *
     * @return $this
     */
    public function checkForInvalidTextures()
    {
        foreach (self::$types as $type) {
            $property = "tid_$type";

            if (! Texture::find($this->$property)) {
                // reset texture
                $this->$property = 0;
            }
        }

        $this->save();

        return $this;
    }

    /**
     * Clear the textures of player.
     *
     * @param  array|string $types
     * @return $this
     */
    public function clearTexture($types)
    {
        $types = (array) $types;

        $map = [];

        foreach ($types as $type) {
            $map["tid_$type"] = 0;
        }

        $this->setTexture($map);

        return $this;
    }

    /**
     * Rename the player.
     *
     * @param  string $newName
     * @return $this
     */
    public function rename($newName)
    {
        $this->update([
            'player_name'   => $newName,
            'last_modified' => get_datetime_string()
        ]);

        $this->player_name = $newName;

        event(new PlayerProfileUpdated($this));

        return $this;
    }

    /**
     * Set a new owner for the player.
     *
     * @param  int $uid
     * @return $this
     */
    public function setOwner($uid) {
        $this->update(['uid' => $uid]);

        event(new PlayerProfileUpdated($this));

        return $this;
    }

    /**
     * Get Json profile of player.
     *
     * @param  int $api_type Which API to use, 0 for CustomSkinAPI, 1 for UniSkinAPI
     * @return string        User profile in json format
     */
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

    /**
     * Generate player profile in json format.
     *
     * @param  int $api_type
     * @return string
     */
    public function generateJsonProfile($api_type)
    {
        $json[($api_type == self::CSL_API) ? 'username' : 'player_name'] = $this->player_name;

        $texture = Texture::find($this->tid_skin);
        $model = empty($texture) ? 'default' : ($texture->type === 'steve' ? 'default' : 'slim');

        if ($api_type == self::USM_API) {
            $json['last_update']      = strtotime($this->last_modified);
            $json['model_preference'] = [$model];
        }

        $skinHash = $this->getTexture('skin');
        $json['skins']['default'] = $skinHash;
        $json['skins'][$model] = $skinHash;
        $json['cape'] = $this->getTexture('cape');

        return json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Update the date of last modified.
     *
     * @return mixed
     */
    public function updateLastModified()
    {
        // @see http://stackoverflow.com/questions/2215354/php-date-format-when-inserting-into-datetime-in-mysql
        $this->update(['last_modified' => get_datetime_string()]);
        return event(new PlayerProfileUpdated($this));
    }
}
