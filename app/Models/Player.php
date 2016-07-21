<?php

namespace App\Models;

use App\Exceptions\E;
use Utils;

class Player
{
    public $pid            = "";
    public $player_name    = "";

    public $eloquent_model = null;

    /**
     * Construct player with pid or playername
     * @param int $pid
     * @param string $player_name
     */
    public function __construct($pid, $player_name = "")
    {
        if ($player_name == "") {
            $this->pid = $pid;
            $this->eloquent_model = PlayerModel::find($pid);
        } else {
            $this->eloquent_model = PlayerModel::where('player_name', $player_name)->first();
            @$this->pid = $this->eloquent_model->pid;
        }

        if (!$this->eloquent_model)
            \Http::abort(404, '角色不存在');

        $this->player_name = $this->eloquent_model->player_name;
    }

    /**
     * Get textures of player
     * @param  string $type steve|alex|cape, 'skin' for texture of preferred model
     * @return string sha256-hash of texture file
     */
    public function getTexture($type)
    {
        if ($type == "skin")
            $type = ($this->getPreference() == "default") ? "steve" : "alex";
        if ($type == "steve" | $type == "alex" | $type == "cape") {
            $tid = $this->eloquent_model['tid_'.$type];
            return Texture::find($tid)['hash'];
        }
        return false;
    }

    public function setTexture(Array $tids)
    {
        if (!isset($tids['tid_steve']) && !isset($tids['tid_alex']) && !isset($tids['tid_cape']))
        {
            throw new E('Invalid parameters.', 1);
        }

        $this->eloquent_model->tid_steve = isset($tids['tid_steve']) ? $tids['tid_steve'] : $this->eloquent_model['tid_steve'];
        $this->eloquent_model->tid_alex  = isset($tids['tid_alex'])  ? $tids['tid_alex']  : $this->eloquent_model['tid_alex'];
        $this->eloquent_model->tid_cape  = isset($tids['tid_cape'])  ? $tids['tid_cape']  : $this->eloquent_model['tid_cape'];

        $this->eloquent_model->last_modified = Utils::getTimeFormatted();
        return $this->eloquent_model->save();
    }

    public function getBinaryTexture($type)
    {
        if ($this->getTexture($type) != "") {
            $filename = BASE_DIR."/textures/".$this->getTexture($type);

            if (file_exists($filename)) {
                header('Content-Type: image/png');
                // Cache friendly
                header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $this->getLastModified()).' GMT');
                header('Content-Length: '.filesize($filename));

                return \Storage::fread($filename);
            } else {
                \Http::abort(404, '请求的贴图已被删除。');
            }
        } else {
            \Http::abort(404, '该用户尚未上传请求的贴图类型 '.$type);
        }
    }

    /**
     * Set preferred model
     * @param string $type, 'slim' or 'default'
     */
    public function setPreference($type) {

        return $this->eloquent_model->update([
            'preference'    => $type,
            'last_modified' => Utils::getTimeFormatted()
        ]);
    }

    public function getPreference() {
        return $this->eloquent_model['preference'];
    }

    /**
     * Get JSON profile
     * @param  int $api_type, which API to use, 0 for CustomSkinAPI, 1 for UniSkinAPI
     * @return string, user profile in json format
     */
    public function getJsonProfile($api_type) {
        header('Content-type: application/json');

        // Support both CustomSkinLoader API & UniSkinAPI
        if ($api_type == 0 || $api_type == 1) {
            $json[($api_type == 0) ? 'username' : 'player_name'] = $this->player_name;
            $model = $this->getPreference();
            $sec_model = ($model == 'default') ? 'slim' : 'default';
            if ($api_type == 1) {
                $json['last_update'] = $this->getLastModified();
                $json['model_preference'] = [$model, $sec_model];
            }
            if ($this->getTexture('steve') || $this->getTexture('alex')) {
                // Skins dict order by preference model
                $json['skins'][$model] = $this->getTexture($model == "default" ? "steve" : "alex");
                $json['skins'][$sec_model] = $this->getTexture($sec_model == "default" ? "steve" : "alex");
            }
            $json['cape'] = $this->getTexture('cape');
        } else {
            throw new E('不支持的 API_TYPE。', -1, true);
        }

        return json_encode($json, JSON_PRETTY_PRINT);
    }

    public function updateLastModified() {
        // @see http://stackoverflow.com/questions/2215354/php-date-format-when-inserting-into-datetime-in-mysql
        return $this->eloquent_model->update(['last_modified' => Utils::getTimeFormatted()]);
    }

    /**
     * Get last modified time
     * @return timestamp
     */
    public function getLastModified() {
        return strtotime($this->eloquent_model['last_modified']);
    }
}

class PlayerModel extends \Illuminate\Database\Eloquent\Model
{
    public $primaryKey = 'pid';
    protected $table = 'players';
    public $timestamps = false;

    protected $fillable = ['preference', 'last_modified'];
}
