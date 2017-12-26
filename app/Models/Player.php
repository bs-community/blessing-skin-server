<?php

namespace App\Models;

use Event;
use Utils;
use Storage;
use Response;
use App\Models\User;
use App\Events\GetPlayerJson;
use App\Events\PlayerProfileUpdated;
use App\Exceptions\PrettyPageException;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
     * @param  string $type steve|alex|cape
     * @return string       Sha256-hash of texture file.
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

        $this->last_modified = Utils::getTimeFormatted();

        $this->save();

        event(new PlayerProfileUpdated($this));

        return $this;
    }

    /**
     * Check and delete invalid textures from player profile.
     *
     * @return mixed
     */
    public function checkForInvalidTextures()
    {
        foreach (self::$models as $model) {
            $property = "tid_$model";

            if (!Texture::find($this->$property)) {
                // reset texture
                $this->$property = 0;
            }
        }

        return $this->save();
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
     * Get binary texture by type.
     *
     * @param  string $type steve|alex|cape
     * @return \Illuminate\Http\Response
     */
    public function getBinaryTexture($type)
    {
        if ($this->getTexture($type)) {
            $hash = $this->getTexture($type);

            if (Storage::disk('textures')->has($hash)) {
                // Cache friendly
                return Response::png(Storage::disk('textures')->get($hash), 200, [
                    'Last-Modified'  => $this->getLastModified(),
                    'Accept-Ranges'  => 'bytes',
                    'Content-Length' => Storage::disk('textures')->size($hash),
                ]);
            } else {
                throw new NotFoundHttpException(trans('general.texture-deleted'));
            }
        } else {
            throw new NotFoundHttpException(trans('general.texture-not-uploaded', ['type' => $type]));
        }
    }

    /**
     * Set preferred model for the player.
     *
     * @param string $type slim|default
     *
     * @return $this
     */
    public function setPreference($type)
    {
        $this->update([
            'preference'    => $type,
            'last_modified' => Utils::getTimeFormatted()
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
     * @param  string $new_name
     * @return $this;
     */
    public function rename($new_name)
    {
        $this->update([
            'player_name'   => $new_name,
            'last_modified' => Utils::getTimeFormatted()
        ]);

        $this->player_name = $new_name;

        event(new PlayerProfileUpdated($this));

        return $this;
    }

    /**
     * Set a new owner for the player.
     *
     * @param int $uid
     *
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

            // if listeners return nothing
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
            $json['last_update']      = $this->getLastModified();
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
        $this->update(['last_modified' => Utils::getTimeFormatted()]);
        return event(new PlayerProfileUpdated($this));
    }

    /**
     * Get time of last modified.
     *
     * @return int|false
     */
    public function getLastModified()
    {
        return strtotime($this['last_modified']);
    }

}
