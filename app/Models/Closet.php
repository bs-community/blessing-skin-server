<?php

namespace App\Models;

use App\Exceptions\E;
use Utils;

class Closet
{
    public $uid;

    /**
     * Instance of App\Models\ClosetModel
     * @var null
     */
    private $eloquent_model = null;

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
        $this->eloquent_model = ClosetModel::find($uid);
        $this->textures = json_decode($this->eloquent_model->textures, true);
        $this->textures = is_null($this->textures) ? [] : $this->textures;

        $textures_invalid = [];

        foreach ($this->textures as $texture) {
            $result = Texture::find($texture['tid']);
            if ($result) {
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

    public function getAmount()
    {
        return $this->eloquent_model->amount;
    }

    public function add($tid)
    {
        foreach ($this->textures as $item) {
            if ($item['tid'] == $tid)
                throw new E('你已经收藏过这个材质啦', 1);
        }

        $this->textures[] = array(
            'tid' => $tid,
            'add_at' => Utils::getTimeFormatted()
        );

        $this->eloquent_model->amount += 1;
        $this->eloquent_model->textures = json_encode($this->textures);
        return $this->eloquent_model->save();
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
                $this->eloquent_model->amount -= 1;
                $this->eloquent_model->textures = json_encode($this->textures);
                return $this->eloquent_model->save();
            }
            $offset++;
        }

        throw new E('The texture is not in the closet.', 1);
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
