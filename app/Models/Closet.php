<?php

namespace App\Models;

use DB;

class Closet
{
    public $uid;

    /**
     * Instance of Query Builder.
     *
     * @var \Illuminate\Database\Query\Builder
     */
    private $db;

    /**
     * Textures array generated from json.
     *
     * @var Array
     */
    private $textures       = [];

    /**
     * Array of App\Models\Texture instances.
     *
     * @var array
     */
    private $textures_skin  = [];

    /**
     * Array of App\Models\Texture instances.
     *
     * @var array
     */
    private $textures_cape  = [];

    /**
     * Items that are modified.
     *
     * @var array
     */
    private $items_modified = [];

    /**
     * Construct Closet object with owner's uid.
     *
     * @param int $uid
     */
    public function __construct($uid)
    {
        $this->uid = $uid;
        $this->db  = DB::table('closets');

        // create a new closet if not exists
        if (!$this->db->where('uid', $uid)->get()) {
            $this->db->insert([
                'uid'      => $uid,
                'textures' => ''
            ]);
        }

        // load items from json string
        $this->textures = json_decode($this->db->where('uid', $uid)->get()[0]->textures, true);
        $this->textures = is_array($this->textures) ? $this->textures : [];

        $textures_invalid = [];

        // traverse items in the closet
        foreach ($this->textures as $texture) {
            $result = Texture::find($texture['tid']);

            if ($result) {
                // set user custom texture name
                $result->name = $texture['name'];

                // push instances of App\Models\Texture to the bag
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

        // remove invalid textures from closet
        foreach ($textures_invalid as $tid) {
            $this->remove($tid);
        }

        unset($textures_invalid);
    }

    /**
     * Get array of instances of App\Models\Texture.
     *
     * @param  string $category skin|cape|all
     * @return array
     */
    public function getItems($category = "all")
    {
        if ($category == "all") {
            $items = array_merge($this->textures_skin, $this->textures_cape);
        } else {
            $property = "textures_$category";
            $items = $this->$property;
        }

        // reverse the array to sort desc by add_at
        return array_reverse($items);
    }

    /**
     * Add an item to the closet.
     *
     * @param  int $tid
     * @param  string $name
     * @return bool
     */
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

        $this->items_modified[] = $tid;

        return true;
    }

    /**
     * Check if texture is in the closet.
     *
     * @param  int  $tid
     * @return bool
     */
    public function has($tid)
    {
        foreach ($this->textures as $item) {
            if ($item['tid'] == $tid) return true;
        }
        return false;
    }

    /**
     * Rename closet item.
     *
     * @param  integer $tid
     * @param  string $new_name
     * @return void
     */
    public function rename($tid, $new_name)
    {
        $offset = 0;
        foreach ($this->textures as $item) {
            if ($item['tid'] == $tid) {
                $this->textures[$offset]['name'] = $new_name;
            }
            $offset++;
        }

        $this->items_modified[] = $tid;

        return true;
    }

    /**
     * Remove a texture from closet.
     * @param  int $tid
     * @return boolean
     */
    public function remove($tid)
    {
        $offset = 0;

        // traverse items
        foreach ($this->textures as $item) {
            if ($item['tid'] == $tid) {
                $this->items_modified[] = $tid;
                // remove element from array
                return array_splice($this->textures, $offset, 1);
            }
            $offset++;
        }

        return false;
    }

    /**
     * Check if given tid is valid.
     *
     * @param  int $tid
     * @return bool
     */
    private function checkTextureExist($tid)
    {
        return ! Texture::where('tid', $tid)->isEmpty();
    }

    /**
     * Set textures string manually.
     *
     * @param string $textures
     */
    public function setTextures($textures)
    {
        return $this->db->where('uid', $this->uid)->update(['textures' => $textures]);;
    }

    /**
     * Do really database operations.
     *
     * @return bool
     */
    public function save()
    {
        if (empty($this->items_modified)) return;

        return $this->setTextures(json_encode($this->textures));
    }

    /**
     * Save when the object will be destructed.
     */
    public function __destruct()
    {
        $this->save();
    }

    /**
     * Get all closets.
     *
     * @return array
     */
    public static function all()
    {
        $result = [];
        foreach (DB::table('closets')->lists('uid') as $uid) {
            $result[] = new Closet($uid);
        }
        return $result;
    }

}
