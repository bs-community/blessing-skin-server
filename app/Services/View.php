<?php

namespace App\Services;

use Session;

/**
 * @see \Illuminate\Support\Facades\View
 */
class View extends \Illuminate\Support\Facades\View
{
    public static function show($view, $data = [], $mergeData = [])
    {
        echo self::make($view, $data, $mergeData)->render();
    }

    // function reload
    public static function json()
    {
        @header('Content-type: application/json; charset=utf-8');
        $args = func_get_args();
        if (count($args) == 1) {
            self::jsonCustom($args[0]);
        } elseif(count($args) == 2) {
            self::jsonException($args[0], $args[1]);
        }
    }

    private static function jsonCustom(Array $array)
    {
        if (is_array($array)) {
            Session::save();
            exit(json_encode($array));
        }
    }

    private static function jsonException($msg, $errno)
    {
        Session::save();
        exit(json_encode([
            'errno' => $errno,
            'msg' => $msg
        ]));
    }
}
