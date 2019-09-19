<?php

declare(strict_types=1);

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

if (! function_exists('plugin')) {
    function plugin(string $name)
    {
        return app('plugins')->get($name);
    }
}

if (! function_exists('plugin_assets')) {
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

if (! function_exists('json')) {
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

if (! function_exists('option')) {
    /**
     * Get / set the specified option value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     * @param  array|string  $key
     * @param  mixed  $default
     * @param  raw    $raw  return raw value without convertion
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

if (! function_exists('option_localized')) {
    function option_localized($key = null, $default = null, $raw = false)
    {
        return option($key.'_'.config('app.locale'), option($key));
    }
}

if (! function_exists('humanize_db_type')) {
    function humanize_db_type($type = null): string
    {
        $map = [
            'mysql'  => 'MySQL/MariaDB',
            'sqlite' => 'SQLite',
            'pgsql'  => 'PostgreSQL',
        ];

        $type = $type ?: config('database.default');

        return Arr::get($map, $type, '');
    }
}

if (! function_exists('get_db_config')) {
    function get_db_config($type = null)
    {
        $type = $type ?: config('database.default');

        return config("database.connections.$type");
    }
}

if (! function_exists('get_datetime_string')) {
    /**
     * Get date time string in "Y-m-d H:i:s" format.
     *
     * @param int $timestamp
     * @return string
     */
    function get_datetime_string($timestamp = 0): string
    {
        return $timestamp == 0 ? Carbon::now()->toDateTimeString() : Carbon::createFromTimestamp($timestamp)->toDateTimeString();
    }
}

if (! function_exists('get_client_ip')) {
    /**
     * Return the client IP address.
     *
     * We define this function because Symfony's "Request::getClientIp()" method
     * needs "setTrustedProxies()", which sucks when load balancer is enabled.
     *
     * @return string
     */
    function get_client_ip(): string
    {
        $request = request();
        if (option('ip_get_method') == '0') {
            $ip = $request->server('HTTP_X_FORWARDED_FOR')
                ?? $request->server('HTTP_CLIENT_IP')
                ?? $request->server('REMOTE_ADDR');
        } else {
            $ip = $request->server('REMOTE_ADDR');
        }

        return $ip;
    }
}

if (! function_exists('get_string_replaced')) {
    /**
     * Replace content of string according to given rules.
     *
     * @param  string $str
     * @param  array  $rules
     * @return string
     */
    function get_string_replaced(string $str, array $rules): string
    {
        foreach ($rules as $search => $replace) {
            $str = str_replace($search, $replace, $str);
        }

        return $str;
    }
}

if (! function_exists('is_request_secure')) {
    /**
     * Check whether the request is secure or not.
     * True is always returned when "X-Forwarded-Proto" header is set.
     *
     * We define this function because Symfony's "Request::isSecure()" method
     * needs "setTrustedProxies()" which sucks when load balancer is enabled.
     *
     * @return bool
     */
    function is_request_secure(): bool
    {
        $request = request();
        return $request->server('HTTPS') === 'on'
            || $request->server('HTTP_X_FORWARDED_PROTO') === 'https'
            || $request->server('HTTP_X_FORWARDED_SSL') === 'on';
    }
}

if (! function_exists('png')) {
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
