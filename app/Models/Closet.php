<?php

namespace App\Models;

use App\Exceptions\PrettyPageException;
use Utils;
use View;

class Closet
{
    public $uid;

    /**
     * Instance of App\Models\ClosetModel
     * @var null
     */
    private $model          = null;

    /**
     * Textures array generated from json
     * @var Array
     */
    private $textures       = [];

    /**
     * Array of App\Models\Texture instances
     * @var array
     */
    private $textures_skin  = [];

    /**
     * Array of App\Models\Texture instances
     * @var array
     */
    private $textures_cape  = [];

    /**
     * Construct Closet object with owner's uid
     * @param int $uid
     */
    function __construct($uid)
    {
        $this->uid = $uid;
        $this->model = ClosetModel::find($uid);

        if ($this->model) {
            $this->textures = json_decode($this->model->textures, true);
            $this->textures = is_null($this->textures) ? [] : $this->textures;

            $textures_invalid = [];

            foreach ($this->textures as $texture) {
                $result = Texture::find($texture['tid']);
                if ($result) {
                    // user custom texture name
                    $result->name = $texture['name'];

                    if ($result->type == "cape") {
                        $this->textures_cape[] = $result;
                    } else {
                        $this->textures_skin[] = $result;
                    }
                } else {
                    $textures_invalid[] = $texture['tid'];
                    continue;
                }
            }

            foreach ($textures_invalid as $tid) {
                $this->remove($tid);
            }

            unset($textures_invalid);
        } else {
            $this->model = new ClosetModel();
            $this->model->uid = $uid;
            $this->model->save();
        }

    }

    /**
     * Get array of instances of App\Models\Texture
     * @param  string $category
     * @return array
     */
    public function getItems($category = "skin")
    {
        // need to reverse the array to sort desc by add_at
        return array_reverse(($category == "skin") ? $this->textures_skin : $this->textures_cape);
    }

    public function add($tid, $name)
    {
        foreach ($this->textures as $item) {
            if ($item['tid'] == $tid)
                return false;
        }

        $this->textures[] = array(
            'tid'    => $tid,
            'name'   => $name,
            'add_at' => time()
        );

        $this->model->textures = json_encode($this->textures);
        return $this->model->save();
    }

    /**
     * Check if texture is in the closet
     * @param  int  $tid
     * @return boolean
     */
    public function has($tid)
    {
        foreach ($this->textures as $item) {
            if ($item['tid'] == $tid) return true;
        }
        return false;
    }

    /**
     * Remove a texture from closet
     * @param  int $tid
     * @return boolean
     */
    public function remove($tid)
    {
        $offset = 0;
        // remove array element
        foreach ($this->textures as $item) {
            if ($item['tid'] == $tid) {
                array_splice($this->textures, $offset, 1);
                $this->model->textures = json_encode($this->textures);
                return $this->model->save();
            }
            $offset++;
        }

        return false;
    }

    private function checkTextureExist($tid)
    {
        return (Texture::where('tid', $tid)->count() > 0) ? true : false;
    }

}

class ClosetModel extends \Illuminate\Database\Eloquent\Model
{
    public $primaryKey = 'uid';
    protected $table = 'closets';
    public $timestamps = false;
}
