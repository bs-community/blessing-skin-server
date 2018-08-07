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

    /**
     * Set of models.
     */
    protected static $models = ['steve', 'alex', 'cape'];

    /**
     * Properties for Eloquent Model.
     */
    public    $primaryKey = 'pid';
    public    $timestamps = false;
    protected $fillable   = ['uid', 'player_name', 'preference', 'last_modified'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'pid' => 'integer',
        'uid' => 'integer',
        'tid_steve' => 'integer',
        'tid_alex' => 'integer',
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

    /**
     * Get specific texture of player.
     *
     * @param  string $type "steve" or "alex" or "cape".
     * @return string The sha256 hash of texture file.
     */
    public function getTexture($type)
    {
        if ($type == "skin")
            $type = ($this->getPreference() == "default") ? "steve" : "alex";

        if (in_array($type, self::$models)) {
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
        foreach (self::$models as $model) {
            $property = "tid_$model";

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
        foreach (self::$models as $model) {
            $property = "tid_$model";

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
     * Set preferred model for the player.
     *
     * @param  string $type "slim" or "default".
     * @return $this
     */
    public function setPreference($type)
    {
        $this->update([
            'preference'    => $type,
            'last_modified' => get_datetime_string()
        ]);

        event(new PlayerProfileUpdated($this));

        return $this;
    }

    /**
     * Get model preference of the player.
     *
     * @return string
     */
    public function getPreference()
    {
        return $this['preference'];
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

            $responses = Event::fire(new GetPlayerJson($this, $api_type));

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

        $model     = $this->getPreference();
        $sec_model = ($model == 'default') ? 'slim' : 'default';

        if ($api_type == self::USM_API) {
            $json['last_update']      = strtotime($this->last_modified);
            $json['model_preference'] = [$model, $sec_model];
        }

        if ($this->getTexture('steve') || $this->getTexture('alex')) {
            // Skins dict order by preference model
            $json['skins'][$model]     = $this->getTexture($model == "default" ? "steve" : "alex");
            $json['skins'][$sec_model] = $this->getTexture($sec_model == "default" ? "steve" : "alex");
        }

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
