<?php

namespace App\Services\Repositories;

use DB;
use Illuminate\Support\Arr;
use Illuminate\Database\QueryException;

class OptionRepository extends Repository
{
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
     * @param  string $key
     * @param  mixed  $default
     * @param  bool   $raw  Return raw value without convertion.
     * @return mixed
     */
    public function get($key, $default = null, $raw = false)
    {
        if (! $this->has($key) && Arr::has(config('options'), $key)) {
            $this->set($key, config("options.$key"));
        }

        $value = Arr::get($this->items, $key, $default);

        if ($raw) {
            return $value;
        }

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;

            case 'false':
            case '(false)':
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
     * @param  array|string $key
     * @param  mixed        $value
     * @return void
     */
    public function set($key, $value = null)
    {
        if (is_array($key)) {
            // If given key is an array
            foreach ($key as $innerKey => $innerValue) {
                Arr::set($this->items, $innerKey, $innerValue);
                $this->doSetOption($innerKey, $innerValue);
            }
        } else {
            Arr::set($this->items, $key, $value);
            $this->doSetOption($key, $value);
        }
    }

    /**
     * Do really save modified options to database.
     *
     * @return void
     */
    protected function doSetOption($key, $value)
    {
        try {
            if (! DB::table('options')->where('option_name', $key)->first()) {
                DB::table('options')
                    ->insert(['option_name' => $key, 'option_value' => $value]);
            } else {
                DB::table('options')
                        ->where('option_name', $key)
                        ->update(['option_value' => $value]);
            }
        } catch (QueryException $e) {
            return;
        }
    }

    /**
     * Do really save modified options to database.
     *
     * @deprecated
     * @return void
     */
    public function save()
    {
        $this->itemsModified = array_unique($this->itemsModified);

        try {
            foreach ($this->itemsModified as $key) {
                if (! DB::table('options')->where('option_name', $key)->first()) {
                    DB::table('options')
                        ->insert(['option_name' => $key, 'option_value' => $this[$key]]);
                } else {
                    DB::table('options')
                            ->where('option_name', $key)
                            ->update(['option_value' => $this[$key]]);
                }
            }

            // Clear the list
            $this->itemsModified = [];
        } catch (QueryException $e) {
            return;
        }
    }

    /**
     * Save all modified options into database.
     */
    public function __destruct()
    {
        $this->save();
    }
}
