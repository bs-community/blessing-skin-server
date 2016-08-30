<?php

namespace App\Models;

use App\Exceptions\PrettyPageException;
use App\Events\PlayerProfileUpdated;
use App\Events\GetPlayerJson;
use Event;
use Utils;
use View;

class Player
{
    public $pid         = "";
    public $player_name = "";

    public $is_banned   = false;

    public $model       = null;

    const CSL_API       = 0;
    const USM_API       = 1;

    /**
     * Construct player with pid or playername
     *
     * @param int    $pid
     * @param string $player_name
     */
    public function __construct($pid, $player_name = "")
    {
        if ($player_name == "") {
            $this->pid   = $pid;
            $this->model = PlayerModel::find($pid);
        } else {
            $this->model = PlayerModel::where('player_name', $player_name)->first();
        }

        if (!$this->model) {
            abort(404, '角色不存在');
        } else {
            $this->pid = $this->model->pid;
        }

        $this->player_name = $this->model->player_name;

        if ((new User($this->model->uid))->getPermission() == "-1")
            $this->is_banned = true;
    }

    /**
     * Get textures of player
     *
     * @param  string $type steve|alex|cape, 'skin' for texture of preferred model
     * @return string sha256-hash of texture file
     */
    public function getTexture($type)
    {
        if ($type == "skin")
            $type = ($this->getPreference() == "default") ? "steve" : "alex";
        if ($type == "steve" | $type == "alex" | $type == "cape") {
            $tid = $this->model['tid_'.$type];
            return Texture::find($tid)['hash'];
        }
        return false;
    }

    public function setTexture(Array $tids)
    {
        if (!isset($tids['tid_steve']) && !isset($tids['tid_alex']) && !isset($tids['tid_cape']))
        {
            View::json('非法参数', 1);
        }

        $this->model->tid_steve = isset($tids['tid_steve']) ? $tids['tid_steve'] : $this->model['tid_steve'];
        $this->model->tid_alex  = isset($tids['tid_alex'])  ? $tids['tid_alex']  : $this->model['tid_alex'];
        $this->model->tid_cape  = isset($tids['tid_cape'])  ? $tids['tid_cape']  : $this->model['tid_cape'];

        $this->model->last_modified = Utils::getTimeFormatted();

        $this->model->save();

        return Event::fire(new PlayerProfileUpdated($this));
    }

    public function clearTexture()
    {
        $this->setPreference('default');
        $this->setTexture(['tid_steve' => 0, 'tid_alex' => 0, 'tid_cape' => 0]);
    }

    public function getBinaryTexture($type)
    {
        if ($this->getTexture($type) != "") {
            $hash = $this->getTexture($type);
            $path = BASE_DIR."/storage/textures/".$hash;

            if (\Storage::disk('textures')->has($hash)) {
                // Cache friendly
                return response(\Storage::disk('textures')->get($hash))
                        ->header('Content-Type',  'image/png')
                        ->header('Last-Modified',  gmdate('D, d M Y H:i:s', $this->getLastModified()).' GMT')
                        ->header('Content-Length', filesize($path));
            } else {
                abort(404, '请求的贴图已被删除。');
            }
        } else {
            abort(404, '该用户尚未上传请求的贴图类型 '.$type);
        }
    }

    /**
     * Set preferred model
     * @param string $type, 'slim' or 'default'
     */
    public function setPreference($type) {
        $this->model->update([
            'preference'    => $type,
            'last_modified' => Utils::getTimeFormatted()
        ]);

        return Event::fire(new PlayerProfileUpdated($this));
    }

    public function getPreference() {
        return $this->model['preference'];
    }

    public function rename($new_name)
    {
        $this->model->update([
            'player_name'   => $new_name,
            'last_modified' => Utils::getTimeFormatted()
        ]);

        $this->player_name = $new_name;

        return Event::fire(new PlayerProfileUpdated($this));
    }

    public function setOwner($uid) {
        $this->model->update(['uid' => $uid]);

        return Event::fire(new PlayerProfileUpdated($this));
    }

    /**
     * Get JSON profile
     *
     * @param  int $api_type Which API to use, 0 for CustomSkinAPI, 1 for UniSkinAPI
     * @return string        User profile in json format
     */
    public function getJsonProfile($api_type) {
        // Support both CustomSkinLoader API & UniSkinAPI
        if ($api_type == self::CSL_API || $api_type == self::USM_API) {
            $responses = Event::fire(new GetPlayerJson($this, $api_type));
            // if listeners return nothing
            if (isset($responses[0]) && $responses[0] !== null) {
                return $responses[0];
            } else {
                return $this->generateJsonProfile($api_type);
            }
        } else {
            throw new PrettyPageException('不支持的 API_TYPE。', -1);
        }
    }

    /**
     * Generate player profile in json string
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

        return json_encode($json, JSON_PRETTY_PRINT);
    }

    public function updateLastModified() {
        // @see http://stackoverflow.com/questions/2215354/php-date-format-when-inserting-into-datetime-in-mysql
        $this->model->update(['last_modified' => Utils::getTimeFormatted()]);
        return Event::fire(new PlayerProfileUpdated($this));
    }

    /**
     * Get last modified time
     * @return timestamp
     */
    public function getLastModified() {
        return strtotime($this->model['last_modified']);
    }
}

class PlayerModel extends \Illuminate\Database\Eloquent\Model
{
    public $primaryKey  = 'pid';
    protected $table    = 'players';
    public $timestamps  = false;

    protected $fillable = ['player_name', 'preference', 'last_modified'];

    public function scopeLike($query, $field, $value)
    {
        return $query->where($field, 'LIKE', "%$value%");
    }
}
