<?php

namespace App\Services\Repositories;

use ArrayAccess;
use Illuminate\Support\Arr;

class Repository implements ArrayAccess // Illuminate\Contracts\Cache\Repository
{
    /**
     * All of the items.
     *
     * @var array
     */
    protected $items;

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
     * Set a given option value.
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
                $this->items_modified[] = $innerKey;
            }
        } else {
            Arr::set($this->items, $key, $value);
            $this->items_modified[] = $key;
        }
    }

    /**
     * Push an item into the repository.
     *
     * @param  mixed $item
     * @return void
     */
    public function push($item)
    {
        array_push($this->items, $item);
    }

    /**
     * Get all of the items stored in the repository.
     *
     * @return array
     */
    public function all()
    {
        return $this->items;
    }

    /**
     * Get an item from the repository, or store the default value.
     *
     * @param  string  $key
     * @param  \Closure  $callback
     * @return mixed
     */
    public function remember($key, Closure $callback)
    {
        // If the item exists in the repository we will just return this immediately
        // otherwise we will execute the given Closure and repository the result
        // of that execution for the given number of minutes in storage.
        if (! is_null($value = $this->get($key))) {
            return $value;
        }

        $this->put($key, $value = $callback());

        return $value;
    }

    /**
     * Remove an item from the repository.
     *
     * @param  string $key
     * @return bool
     */
    public function forget($key)
    {
        Arr::forget($this->items, $key);
    }

    /**
     * Determine if the given option option exists.
     *
     * @param  string  $key
     * @return bool
     */
    public function offsetExists($key)
    {
        return $this->has($key);
    }

    /**
     * Get a option option.
     *
     * @param  string  $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * Set a option option.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * Unset a option option.
     *
     * @param  string  $key
     * @return void
     */
    public function offsetUnset($key)
    {
        $this->forget($key);
    }
}
