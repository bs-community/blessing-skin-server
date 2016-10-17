<?php

namespace App\Services;

use DB;
use ArrayAccess;
use Illuminate\Support\Arr;
use Illuminate\Contracts\Config\Repository as ConfigContract;

class OptionRepository implements ArrayAccess, ConfigContract
{
    /**
     * All of the option items.
     *
     * @var array
     */
    protected $items = [];

    protected $items_modified = [];

    /**
     * Create a new option repository.
     *
     * @return void
     */
    public function __construct()
    {
        $options = DB::table('options')->get();

        foreach ($options as $option) {
            $this->items[$option->option_name] = $option->option_value;
        }

    }

    /**
     * Determine if the given option value exists.
     *
     * @param  string  $key
     * @return bool
     */
    public function has($key)
    {
        return Arr::has($this->items, $key);
    }

    /**
     * Get the specified option value.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @param  bool    $bool convert '0', '1' to bool value
     * @return mixed
     */
    public function get($key, $default = null, $bool = true)
    {
        if (!$this->has($key) && Arr::has(config('options'), $key)) {
            $this->set($key, config("options.$key"));
        }

        $value = Arr::get($this->items, $key, $default);

        if (!$bool) return $value;

        switch (strtolower($value)) {
            case 'true':
            case '1':
                return true;

            case 'false':
            case '0':
                return false;

            case 'null':
            case '(null)':
                return;

            default:
                return $value;
                break;
        }
    }

    /**
     * Set a given option value.
     *
     * @param  array|string  $key
     * @param  mixed   $value
     * @return void
     */
    public function set($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $innerKey => $innerValue) {
                Arr::set($this->items, $innerKey, $innerValue);
                $this->items_modified[] = $innerKey;
            }
        } else {
            Arr::set($this->items, $key, $value);
            $this->items_modified[] = $key;
        }
    }

    protected function save()
    {
        $this->items_modified = array_unique($this->items_modified);

        foreach ($this->items_modified as $key) {
            if (!DB::table('options')->where('option_name', $key)->first()) {
                DB::table('options')
                    ->insert(['option_name' => $key, 'option_value' => $this[$key]]);
            } else {
                DB::table('options')
                        ->where('option_name', $key)
                        ->update(['option_value' => $this[$key]]);
            }
        }
    }

    /**
     * Prepend a value onto an array option value.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function prepend($key, $value)
    {
        $array = $this->get($key);

        array_unshift($array, $value);

        $this->set($key, $array);
    }

    /**
     * Push a value onto an array option value.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function push($key, $value)
    {
        $array = $this->get($key);

        $array[] = $value;

        $this->set($key, $array);
    }

    /**
     * Get all of the option items for the application.
     *
     * @return array
     */
    public function all()
    {
        return $this->items;
    }

    public function only(Array $array)
    {
        $result = [];

        foreach ($this->items as $key => $value) {
            if (in_array($key, $array)) {
                $result[$key] = $value;
            }
        }

        return $result;
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
        $this->set($key, null);
    }

    /**
     * Save all modified options into database
     */
    public function __destruct()
    {
        $this->save();
    }

}
