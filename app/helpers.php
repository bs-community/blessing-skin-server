<?php

use Illuminate\Support\Str;
use Illuminate\Support\Arr;

if (! function_exists('get_base_url')) {

    function get_base_url()
    {
        $base_url  = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? "https://" : "http://";
        $base_url .= $_SERVER["SERVER_NAME"];
        $base_url .= ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);

        return $base_url;
    }
}

if (! function_exists('get_current_url')) {

    function get_current_url()
    {
        return get_base_url().$_SERVER["REQUEST_URI"];
    }
}

if (! function_exists('avatar')) {

    function avatar(App\Models\User $user, $size)
    {
        $fname = base64_encode($user->email).".png?tid=".$user->getAvatarId();

        return url("avatar/$size/$fname");
    }
}

if (! function_exists('assets')) {

    function assets($relative_uri)
    {
        // add query string to fresh cache
        if (Str::startsWith($relative_uri, 'css') || Str::startsWith($relative_uri, 'js')) {
            return url("resources/assets/dist/$relative_uri")."?v=".config('app.version');
        } elseif (Str::startsWith($relative_uri, 'lang')) {
            return url("resources/$relative_uri");
        } else {
            return url("resources/assets/$relative_uri");
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
            // the third argument is array of extra fields
            return Response::json(array_merge([
                'errno' => $args[1],
                'msg'   => $args[0]
            ], $args[2]));
        } else {
            return Response::json([
                'errno' => Arr::get($args, 1, 1),
                'msg'   => $args[0]
            ]);
        }
    }
}

if (! function_exists('bs_footer')) {

    function bs_footer($page_identification = "")
    {
        $scripts = [
            assets('js/app.min.js'),
            assets('lang/'.session('locale', config('app.locale')).'/locale.js'),
            assets('js/general.js')
        ];

        if ($page_identification !== "") {
            $scripts[] = assets("js/$page_identification.js");
        }

        foreach ($scripts as $script) {
            echo "<script type=\"text/javascript\" src=\"$script\"></script>";
        }

        if (Session::has('msg')) {
            echo "<script>toastr.info('".Session::pull('msg')."');</script>";
        }

        echo '<script>'.Option::get("custom_js").'</script>';

        $extra_contents = [];

        Event::fire(new App\Events\RenderingFooter($extra_contents));

        echo implode(PHP_EOL, $extra_contents);
    }
}

if (! function_exists('bs_header')) {

    function bs_header($page_identification = "")
    {
        $styles = [
            assets('css/app.min.css'),
            assets('vendor/skins/'.Option::get('color_scheme').'.min.css')
        ];

        if ($page_identification !== "") {
            $styles[] = assets("css/$page_identification.css");
        }

        foreach ($styles as $style) {
            echo "<link rel=\"stylesheet\" href=\"$style\">";
        }

        echo '<style>'.Option::get("custom_css").'</style>';

        $extra_contents = [];

        Event::fire(new App\Events\RenderingHeader($extra_contents));

        echo implode(PHP_EOL, $extra_contents);
    }
}

if (! function_exists('bs_menu')) {

    function bs_menu($type)
    {
        $menu = config('menu');

        event($type == "user" ? new App\Events\ConfigureUserMenu($menu)
                                : new App\Events\ConfigureAdminMenu($menu));

        if (!isset($menu[$type])) {
            throw new InvalidArgumentException;
        }

        echo bs_menu_render($menu[$type]);
    }

    function bs_menu_render($data)
    {
        $content = "";

        foreach ($data as $key => $value) {
            $content .= (app('request')->is(@$value['link'])) ? '<li class="active">' : '<li>';

            if (isset($value['children'])) {
                $content .= '<a href="#"><i class="fa '.$value['icon'].'"></i> <span>'.trans($value['title']).'</span><span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a>';
                // recurse
                $content .= '<ul class="treeview-menu" style="display: none;">'.bs_menu_render($value['children']).'</ul>';
            } else {
                $content .= '<a href="'.url($value['link']).'"><i class="fa '.$value['icon'].'"></i> <span>'.trans($value['title']).'</span></a>';
            }

            $content .= '</li>';
        }

        return $content;
    }
}

if (! function_exists('bs_copyright')) {

    function bs_copyright()
    {
        return Utils::getStringReplaced(Option::get('copyright_text'), ['{site_name}' => Option::get('site_name'), '{site_url}' => Option::get('site_url')]);
    }
}

if (! function_exists('bs_nickname')) {

    function bs_nickname(\App\Models\User $user)
    {
        return ($user->getNickName() == '') ? $user->email : $user->getNickName();
    }
}

if (! function_exists('option')) {

    function option($key = null, $default = null)
    {
        $options = app('options');

        if (is_null($key)) {
            return $options;
        }

        if (is_array($key)) {
            foreach ($key as $innerKey => $innerValue) {
                $options->set($innerKey, $innerValue);
            }
            return $options->save();
        }

        return $options->get($key);
    }
}

if (! function_exists('menv')) {
    /**
     * Gets the value of an environment variable by getenv() or $_ENV.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    function menv($key, $default = null)
    {
        if (function_exists('putenv') && function_exists('getenv')) {
            // try to read by getenv()
            $value = getenv($key);

            if ($value === false) {
                return value($default);
            }
        } else {
            // try to read from $_ENV or $_SERVER
            if (isset($_ENV[$key])) {
                $value = $_ENV[$key];
            } elseif (isset($_SERVER[$key])) {
                $value = $_SERVER[$key];
            } else {
                return value($default);
            }
        }

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return;
        }

        if (strlen($value) > 1 && Str::startsWith($value, '"') && Str::endsWith($value, '"')) {
            return substr($value, 1, -1);
        }

        return $value;
    }
}

if (! function_exists('validate')) {

    function validate($value, $type)
    {
        switch ($type) {
            case 'email':
                return (bool) filter_var($value, FILTER_VALIDATE_EMAIL);
                break;

            default:
                # code...
                break;
        }
    }
}

if (! function_exists('delete_cookies')) {

    function delete_cookies()
    {
        Cookie::queue(Cookie::forget('uid'));
        Cookie::queue(Cookie::forget('token'));
    }
}

if (! function_exists('delete_sessions')) {

    function delete_sessions()
    {
        Session::forget('uid');
        Session::forget('token');

        Session::save();
    }
}
