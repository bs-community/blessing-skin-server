<?php

namespace App\Models;

use DB;
use Illuminate\Support\Collection;

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
     * @var Collection
     */
    private $textures;

    /**
     * Indicates if closet has been modified
     *
     * @var array
     */
    private $closet_modified = false;

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
                'textures' => '[]'
            ]);
        }

        // load items from json string
        $this->textures = collect(json_decode(
            $this->db->where('uid', $uid)->first()->textures,
            true
        ));

        // traverse items in the closet
        $this->textures->filter(function ($texture) {
            return is_null(Texture::find($texture['tid']));
        })->each(function ($tid) {
            $this->remove($tid);
        });
    }

    /**
     * Get array of instances of App\Models\Texture.
     *
     * @param  string $category skin|cape|all
     * @return array
     */
    public function getItems($category = "all")
    {
        $textures = Texture::whereIn('tid', $this->textures->pluck('tid')->all())
                        ->get()
                        ->map(function ($texture) {
                            $in_closet = $this->textures
                                            ->where('tid', $texture->tid)
                                            ->first();
                            return [
                                'tid' => $texture->tid,
                                'name' => $in_closet['name'],
                                'type' => $texture->type,
                                'add_at' => $in_closet['add_at']
                            ];
                        })
                        ->sortByDesc('add_at');
        if ($category == "all") {
            return $textures->values()->all();
        } elseif ($category == 'cape') {
            return $textures->filter(function ($texture) {
                return $texture['type'] == 'cape';
            })->values();
        } else {
            return $textures->reject(function ($texture) {
                return $texture['type'] == 'cape';
            })->values();
        }
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
        if ($this->has($tid)) {
            return false;
        }

        $this->textures->push([
            'tid'    => $tid,
            'name'   => $name,
            'add_at' => time()
        ]);

        $this->closet_modified = true;

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
        return $this->textures->contains('tid', $tid);
    }

    /**
     * Get one texture info
     *
     * @param $tid Texture ID
     * @return array|null Result
     */
    public function get($tid)
    {
        return $this->textures->where('tid', $tid)->first();
    }

    /**
     * Rename closet item.
     *
     * @param  integer $tid
     * @param  string $new_name
     * @return bool
     */
    public function rename($tid, $new_name)
    {
        if (!$this->has($tid)) {
            return false;
        }

        $this->textures->transform(function ($texture) use ($tid, $new_name) {
            if ($texture['tid'] == $tid) {
                $texture['name'] = $new_name;
            }
            return $texture;
        });

        $this->closet_modified = true;

        return true;
    }

    /**
     * Remove a texture from closet.
     * @param  int $tid
     * @return boolean
     */
    public function remove($tid)
    {
        if (!$this->has($tid)) {
            return false;
        }
        $this->textures = $this->textures->reject(function ($texture) use ($tid) {
            return $texture['tid'] == $tid;
        });

        $this->closet_modified = true;
        return true;
    }

    /**
     * Set textures string manually.
     *
     * @param string $textures
     * @return int
     */
    public function setTextures($textures)
    {
        return $this->db->where('uid', $this->uid)->update(['textures' => $textures]);
    }

    /**
     * Do really database operations.
     *
     * @return bool
     */
    public function save()
    {
        if (!$this->closet_modified) return false;

        return $this->setTextures($this->textures->toJson());
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
