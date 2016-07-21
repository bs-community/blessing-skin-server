<?php

namespace App\Services;

/**
 * Just a wrapper for Blade template engine
 */
class View
{
    public static function show($view, $data = [], $mergeData = [])
    {
        echo self::make($view, $data, $mergeData)->render();
    }

    public static function make($view, $data = [], $mergeData = [])
    {
        $config = require BASE_DIR."/config/view.php";
        $view_path = [$config['view_path']];
        $cache_path = $config['cache_path'];

        $compiler = new \Xiaoler\Blade\Compilers\BladeCompiler($cache_path);

        $engine = new \Xiaoler\Blade\Engines\CompilerEngine($compiler);
        $finder = new \Xiaoler\Blade\FileViewFinder($view_path);

        $finder->addExtension('tpl');

        $factory = new \Xiaoler\Blade\Factory($engine, $finder);

        return $factory->make($view, $data, $mergeData);
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

    private static function jsonCustom($array)
    {
        if (is_array($array))
            exit(json_encode($array));
        else
            throw new \Exception('The given arugument should be array.');
    }

    private static function jsonException($msg, $errno)
    {
        exit(json_encode([
            'errno' => $errno,
            'msg' => $msg
        ]));
    }
}
