<?php

namespace App\Services\Repositories;

use DB;
use Illuminate\Support\Arr;
use Illuminate\Database\QueryException;

class OptionRepository extends Repository
{
    /**
     * All of the option items  that is modified.
     *
     * @var array
     */
    protected $items_modified = [];

    /**
     * Create a new option repository.
     *
     * @return void
     */
    public function __construct()
    {
        try {
            $options = DB::table('options')->get();
        } catch (QueryException $e) {
            $options = [];
        }

        foreach ($options as $option) {
            $this->items[$option->option_name] = $option->option_value;
        }

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
     * Do really save modified options to database.
     *
     * @return void
     */
    protected function save()
    {
        $this->items_modified = array_unique($this->items_modified);

        try {
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
        } catch (QueryException $e) {
            return;
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
     * Return the options with key in the given array.
     *
     * @param  array  $array
     * @return array
     */
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
     * Save all modified options into database
     */
    public function __destruct()
    {
        $this->save();
    }

}
