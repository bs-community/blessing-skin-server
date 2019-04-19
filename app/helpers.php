<?php

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Arr;

if (! function_exists('webpack_assets')) {
    function webpack_assets($relativeUri)
    {
        if (app()->environment('development')) {
            return "http://127.0.0.1:8080/$relativeUri"; // @codeCoverageIgnore
        } else {
            $path = app('webpack')->$relativeUri;
            $cdn = option('cdn_address');

            return $cdn ? "$cdn/app/$path" : url("/app/$path");
        }
    }
}

if (! function_exists('plugin')) {

    /**
     * @param string $id
     * @return \App\Services\Plugin
     */
    function plugin($id)
    {
        return app('plugins')->getPlugin($id);
    }
}

if (! function_exists('plugin_assets')) {
    function plugin_assets($id, $relativeUri)
    {
        if ($plugin = plugin($id)) {
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

        if (count($args) == 1 && is_array($args[0])) {
            return Response::json($args[0]);
        } elseif (count($args) == 3 && is_array($args[2])) {
            // The third argument is array of extra fields
            return Response::json(array_merge([
                'errno' => $args[1],
                'msg'   => $args[0],
            ], $args[2]));
        } else {
            return Response::json([
                'errno' => Arr::get($args, 1, 1),
                'msg'   => $args[0],
            ]);
        }
    }
}

if (! function_exists('bs_footer_extra')) {
    function bs_footer_extra()
    {
        $extraContents = [];

        Event::dispatch(new App\Events\RenderingFooter($extraContents));

        return implode("\n", $extraContents);
    }
}

if (! function_exists('bs_header_extra')) {
    function bs_header_extra()
    {
        $extraContents = [];

        Event::dispatch(new App\Events\RenderingHeader($extraContents));

        return implode("\n", $extraContents);
    }
}

if (! function_exists('bs_menu')) {
    function bs_menu($type)
    {
        $menu = config('menu');

        Event::dispatch($type == 'user' ? new App\Events\ConfigureUserMenu($menu)
                                : new App\Events\ConfigureAdminMenu($menu));

        if (! isset($menu[$type])) {
            throw new InvalidArgumentException;
        }

        $menu[$type] = array_map(function ($item) {
            if (Arr::get($item, 'id') === 'plugin-configs') {
                $availablePluginConfigs = [];

                foreach (app('plugins')->getEnabledPlugins() as $plugin) {
                    if ($plugin->hasConfigView()) {
                        $availablePluginConfigs[] = [
                            'title' => trans($plugin->title),
                            'link'  => 'admin/plugins/config/'.$plugin->name,
                            'icon'  => 'fa-circle',
                        ];
                    }
                }

                // Don't display this menu item when no plugin config is available
                if (count($availablePluginConfigs) > 0) {
                    $item['children'] = array_merge($item['children'], $availablePluginConfigs);

                    return $item;
                }
            } else {
                return $item;
            }
        }, $menu[$type]);

        return bs_menu_render($menu[$type]);
    }

    function bs_menu_render($data)
    {
        $content = '';

        foreach ($data as $key => $value) {
            $active = app('request')->is(@$value['link']);

            // also set parent as active if any child is active
            foreach ((array) @$value['children'] as $childKey => $childValue) {
                if (app('request')->is(@$childValue['link'])) {
                    $active = true;
                }
            }

            $classes = [];
            $active ? ($classes[] = 'active menu-open') : null;
            isset($value['children']) ? ($classes[] = 'treeview') : null;

            $attr = count($classes) ? sprintf(' class="%s"', implode(' ', $classes)) : '';

            $content .= "<li{$attr}>";

            if (isset($value['children'])) {
                $content .= sprintf('<a href="#"><i class="fas %s"></i> &nbsp;<span>%s</span><span class="pull-right-container"><i class="fas fa-angle-left pull-right"></i></span></a>', $value['icon'], trans($value['title']));

                // recurse
                $content .= '<ul class="treeview-menu">'.bs_menu_render($value['children']).'</ul>';
            } else {
                if ($value) {
                    $content .= sprintf(
                        '<a href="%s"><i class="%s %s"></i> &nbsp;<span>%s</span></a>',
                        url((string) $value['link']),
                        $value['icon'] == 'fa-circle' ? 'far' : 'fas',
                        (string) $value['icon'],
                        trans((string) $value['title'])
                    );
                }
            }

            $content .= '</li>';
        }

        return $content;
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
    function humanize_db_type($type = null)
    {
        $map = [
            'mysql'  => 'MySQL',
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

if (! function_exists('format_http_date')) {
    /**
     * Format a UNIX timestamp to string for HTTP headers.
     *
     * e.g. Wed, 21 Oct 2015 07:28:00 GMT
     *
     * @param int $timestamp
     * @return string
     */
    function format_http_date($timestamp)
    {
        return Carbon::createFromTimestampUTC($timestamp)->format('D, d M Y H:i:s \G\M\T');
    }
}

if (! function_exists('get_datetime_string')) {
    /**
     * Get date time string in "Y-m-d H:i:s" format.
     *
     * @param int $timestamp
     * @return string
     */
    function get_datetime_string($timestamp = 0)
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
    function get_client_ip()
    {
        if (option('ip_get_method') == '0') {
            // Use `HTTP_X_FORWARDED_FOR` if available first
            $ip = Arr::get(
                $_SERVER,
                'HTTP_X_FORWARDED_FOR',
                // Fallback to `HTTP_CLIENT_IP`
                Arr::get(
                    $_SERVER,
                    'HTTP_CLIENT_IP',
                    // Fallback to `REMOTE_ADDR`
                    Arr::get($_SERVER, 'REMOTE_ADDR')
                )
            );
        } else {
            $ip = Arr::get($_SERVER, 'REMOTE_ADDR');
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
    function get_string_replaced($str, $rules)
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
    function is_request_secure()
    {
        if (Arr::get($_SERVER, 'HTTPS') == 'on') {
            return true;
        }

        if (Arr::get($_SERVER, 'HTTP_X_FORWARDED_PROTO') == 'https') {
            return true;
        }

        if (Arr::get($_SERVER, 'HTTP_X_FORWARDED_SSL') == 'on') {
            return true;
        }

        return false;
    }
}

if (! function_exists('nl2p')) {
    /**
     * Wrap blocks of text (delimited by \n) in p tags (similar to nl2br).
     *
     * @param string $text
     * @return string
     */
    function nl2p($text)
    {
        $parts = explode("\n", $text);
        $result = '<p>'.implode('</p><p>', $parts).'</p>';
        // Remove empty paragraphs
        return str_replace('<p></p>', '', $result);
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
