<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use App\Exceptions\E;

class Option
{
    public $timestamps = false;

    public static function get($key) {
        $option = OptionModel::where('option_name', $key)->first();
        if (!$option) throw new E('Unexistent option.', 1);
        return $option->option_value;
    }

    public static function set($key, $value) {
        $option = OptionModel::firstOrCreate('option_name', $key);
        $option->update(['option_value' => $value]);
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
        } finally {
            return true;
        }
    }

    public static function delete($key) {
        OptionModel::where('option_name', $key)->first()->delete();
    }

}

class OptionModel extends Model
{
    protected $table = 'options';
    public $timestamps = false;
}
