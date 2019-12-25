<?php

declare(strict_types=1);

use Illuminate\Support\Arr;

if (!function_exists('plugin')) {
    function plugin(string $name)
    {
        return app('plugins')->get($name);
    }
}

if (!function_exists('plugin_assets')) {
    function plugin_assets(string $name, string $relativeUri): string
    {
        $plugin = plugin($name);
        if ($plugin) {
            return $plugin->assets($relativeUri);
        } else {
            throw new InvalidArgumentException('No such plugin.');
        }
    }
}

if (!function_exists('json')) {
    function json()
    {
        $args = func_get_args();

        if (count($args) === 1 && is_array($args[0])) {
            return response()->json($args[0]);
        } elseif (count($args) === 3 && is_array($args[2])) {
            // The third argument is array of extra fields
            return response()->json([
                'code' => $args[1],
                'message' => $args[0],
                'data' => $args[2],
            ]);
        } else {
            return response()->json([
                'code' => Arr::get($args, 1, 1),
                'message' => $args[0],
            ]);
        }
    }
}

if (!function_exists('option')) {
    /**
     * Get / set the specified option value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     * @param array|string $key
     * @param mixed        $default
     * @param bool         $raw     return raw value without convertion
     *
     * @return mixed
     */
    function option($key = null, $default = null, $raw = false)
    {
        $options = app('options');

        if (is_null($key)) {
            return $options;
        }

        if (is_array($key)) {
            $options->set($key);

            return;
        }

        return $options->get($key, $default, $raw);
    }
}

if (!function_exists('option_localized')) {
    function option_localized($key = null, $default = null, $raw = false)
    {
        return option($key.'_'.config('app.locale'), option($key));
    }
}

if (!function_exists('png')) {
    function png($resource)
    {
        ob_start();
        imagepng($resource);
        $image = ob_get_contents();
        ob_end_clean();
        imagedestroy($resource);

        return $image;
    }
}
