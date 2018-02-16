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

        // Create a new closet if not exists
        if (! $this->db->where('uid', $uid)->get()) {
            $this->db->insert([
                'uid'      => $uid,
                'textures' => '[]'
            ]);
        }

        // Load items from json string
        $this->textures = collect(json_decode(
            $this->db->where('uid', $uid)->first()->textures,
            true
        ));

        // Traverse items in the closet
        $removedCount = $this->textures->filter(function ($texture) use ($uid) {
            $t = Texture::find($texture['tid']);

            // If the texture was deleted
            if (is_null($t)) {
                return true;
            }

            if ($t->public == 0 && $t->uploader != $uid) {
                return true;
            }

            return false;
        })->each(function ($texture) use ($uid) {
            $this->remove($texture['tid']);
        })->count();

        // Return scores if the texture was deleted or set as private
        if (option('return_score')) {
            app('users')->get($uid)->setScore(
                option('score_per_closet_item') * $removedCount,
                'plus'
            );
        }
    }

    /**
     * Get array of instances of App\Models\Texture.
     *
     * @param  string $category "skin" or "cape" or "all".
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
     * @param  int    $tid
     * @param  string $name
     * @return bool
     */
    public function add($tid, $name)
    {
        if ($this->has($tid)) {
            return false;
        }

        $this->textures->push([
            'tid'    => (int) $tid,
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
     * @param  int        $tid
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
     * @param  string  $newName
     * @return bool
     */
    public function rename($tid, $newName)
    {
        if (! $this->has($tid)) {
            return false;
        }

        $this->textures->transform(function ($texture) use ($tid, $newName) {
            if ($texture['tid'] == $tid) {
                $texture['name'] = $newName;
            }
            return $texture;
        });

        $this->closet_modified = true;

        return true;
    }

    /**
     * Remove a texture from closet.
     *
     * @param  int $tid
     * @return bool
     */
    public function remove($tid)
    {
        if (! $this->has($tid)) {
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
        if (! $this->closet_modified) {
            return false;
        }

        return $this->setTextures($this->textures->values()->toJson());
    }

    /**
     * Save when the object will be destructed.
     *
     * @return void
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
