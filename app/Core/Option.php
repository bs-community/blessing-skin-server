<?php

namespace Blessing;

use \Illuminate\Database\Eloquent\Model;
use \Exception;

class Option
{
    public static function get($key, $default_value = null)
    {
        $option = OptionModel::where('option_name', $key)->first();

        if (!$option) {
            if (!is_null($default_value)) {
                return $default_value;
            } else {
                $options = require BASE_DIR."/setup/options.php";

                if (array_key_exists($key, $options)) {
                    self::add($key, $options[$key]);
                    return $options[$key];
                }
                throw new Exception('Unexistent option.', 1);
            }
        }

        return $option->option_value;
    }

    public static function set($key, $value)
    {
        $option = OptionModel::where('option_name', $key)->first();

        if (!$option)
            throw new Exception('Unexistent option.', 1);

        $option->option_value = $value;
        return $option->save();
    }

    public static function add($key, $value)
    {
        if (self::has($key))
            return true;

        $option = new OptionModel;
        $option->option_name  = $key;
        $option->option_value = $value;
        $option->save();
    }

    public static function has($key)
    {
        try {
            OptionModel::where('option_name', $key)->firstOrFail();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return false;
        }
        return true;
    }

    public static function delete($key)
    {
        OptionModel::where('option_name', $key)->delete();
    }

}

class OptionModel extends Model
{
    protected $table = 'options';
    public $timestamps = false;

    protected $fillable = ['option_value'];
}
