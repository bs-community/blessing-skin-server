<?php

namespace App\Services\Repositories;

use Illuminate\Support\Arr;

class Repository
{
    /**
     * All of the items.
     *
     * @var array
     */
    protected $items = [];

    /**
     * All of the option items  that is modified.
     *
     * @var array
     */
    protected $itemsModified = [];

    /**
     * Determine if an item exists in the repository.
     *
     * @param  string  $key
     * @return bool
     */
    public function has($key)
    {
        return Arr::has($this->items, $key);
    }

    /**
     * Retrieve an item from the repository by key.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return Arr::get($this->items, $key, $default);
    }

    /**
     * Set a given item value.
     *
     * @param  array|string  $key
     * @param  mixed  $value
     * @return void
     */
    public function set($key, $value = null)
    {
        if (is_array($key)) {
            // If given key is an array
            foreach ($key as $innerKey => $innerValue) {
                Arr::set($this->items, $innerKey, $innerValue);
                $this->itemsModified[] = $innerKey;
            }
        } else {
            Arr::set($this->items, $key, $value);
            $this->itemsModified[] = $key;
        }
    }
}
