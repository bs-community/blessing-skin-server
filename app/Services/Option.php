<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use App\Exceptions\E;

class Option
{
    public static function get($key) {
        $option = OptionModel::where('option_name', $key)->first();
        if (!$option) throw new E('Unexistent option.', 1);
        return $option->option_value;
    }

    public static function set($key, $value) {
        $option = OptionModel::where('option_name', $key)->first();
        if (!$option) throw new E('Unexistent option.', 1);
        $option->option_value = $value;
        return $option->save();
    }

    public static function add($key, $value) {
        $option = new OptionModel;
        $option->option_name = $key;
        $option->option_value = $value;
        $option->save();
    }

    public static function has($key) {
        try {
            OptionModel::where('option_name', $key)->firstOrFail();
        } catch (Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return false;
        }
        return true;
    }

    public static function delete($key) {
        OptionModel::where('option_name', $key)->first()->delete();
    }

}

class OptionModel extends Model
{
    protected $table = 'options';
    public $timestamps = false;

    protected $fillable = ['option_value'];
}
