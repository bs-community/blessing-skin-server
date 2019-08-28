<?php

declare(strict_types=1);

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

if (! function_exists('webpack_assets')) {
    function webpack_assets(string $relativeUri): string
    {
        if (env('WEBPACK_ENV', 'production') == 'development') {
            // @codeCoverageIgnoreStart
            $host = parse_url(url('/'), PHP_URL_HOST);

            return "http://$host:8080/$relativeUri";
        // @codeCoverageIgnoreEnd
        } else {
            $path = app('webpack')->$relativeUri;
            $cdn = option('cdn_address');

            return $cdn ? "$cdn/app/$path" : url("/app/$path");
        }
    }
}

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

        if (count($args) == 1 && is_array($args[0])) {
            return Response::json($args[0]);
        } elseif (count($args) == 3 && is_array($args[2])) {
            // The third argument is array of extra fields
            return Response::json([
                'code' => $args[1],
                'message' => $args[0],
                'data' => $args[2],
            ]);
        } else {
            return Response::json([
                'code' => Arr::get($args, 1, 1),
                'message' => $args[0],
            ]);
        }
    }
}

if (! function_exists('add_filter')) {
    function add_filter($hook, $callback, $priority = 20, $arguments = 1): void
    {
        app('eventy')->addFilter($hook, $callback, $priority, $arguments);
    }
}

if (! function_exists('apply_filters')) {
    function apply_filters()
    {
        return call_user_func_array([app('eventy'), 'filter'], func_get_args());
    }
}

if (! function_exists('bs_footer_extra')) {
    function bs_footer_extra(): string
    {
        $extraContents = [];

        Event::dispatch(new App\Events\RenderingFooter($extraContents));

        return implode("\n", $extraContents);
    }
}

if (! function_exists('bs_header_extra')) {
    function bs_header_extra(): string
    {
        $extraContents = [];

        Event::dispatch(new App\Events\RenderingHeader($extraContents));

        return implode("\n", $extraContents);
    }
}

if (! function_exists('bs_menu')) {
    function bs_menu(string $type): string
    {
        $menu = config('menu');

        switch ($type) {
            case 'user':
                event(new App\Events\ConfigureUserMenu($menu));
                break;
            case 'explore':
                event(new App\Events\ConfigureExploreMenu($menu));
                break;
            case 'admin':
                event(new App\Events\ConfigureAdminMenu($menu));
                break;
        }

        if (! isset($menu[$type])) {
            throw new InvalidArgumentException;
        }

        $menu[$type] = array_map(function ($item) {
            if (Arr::get($item, 'id') === 'plugin-configs') {
                $pluginConfigs = app('plugins')->getEnabledPlugins()
                    ->filter(function ($plugin) {
                        return $plugin->hasConfigView();
                    })
                    ->map(function ($plugin) {
                        return [
                            'title' => trans($plugin->title),
                            'link'  => 'admin/plugins/config/'.$plugin->name,
                            'icon'  => 'fa-circle',
                        ];
                    });

                // Don't display this menu item when no plugin config is available
                if ($pluginConfigs->isNotEmpty()) {
                    $item['children'] = array_merge($item['children'], $pluginConfigs->values()->all());

                    return $item;
                }
            } else {
                return $item;
            }
        }, $menu[$type]);

        return bs_menu_render($menu[$type]);
    }

    function bs_menu_render(array $data): string
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
                        '<a href="%s" %s><i class="%s %s"></i> &nbsp;<span>%s</span></a>',
                        url((string) $value['link']),
                        Arr::get($value, 'new-tab') ? 'target="_blank"' : '',
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

if (! function_exists('bs_copyright')) {
    function bs_copyright(): string
    {
        return Arr::get(
            [
                'Powered with ❤ by Blessing Skin Server.',
                'Powered by Blessing Skin Server.',
                'Proudly powered by Blessing Skin Server.',
                '由 Blessing Skin Server 强力驱动。',
                '自豪地采用 Blessing Skin Server。',
            ],
            option_localized('copyright_prefer', 0)
        );
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

if (! function_exists('format_http_date')) {
    /**
     * Format a UNIX timestamp to string for HTTP headers.
     *
     * e.g. Wed, 21 Oct 2015 07:28:00 GMT
     *
     * @param int $timestamp
     * @return string
     */
    function format_http_date($timestamp): string
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
    function nl2p(string $text): string
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
