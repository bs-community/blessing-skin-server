<?php

namespace App\Services;

use Session;

class Http
{
    /**
     * HTTP redirect
     *
     * @param  string  $url
     * @param  string  $msg Write message to session
     * @return void
     */
    public static function redirect($url, $msg = "")
    {
        if ($msg !== "") {
            if (app()->bound('session')) {
                Session::flash('msg', $msg);
                Session::save();
            } else {
                $_SESSION['msg'] = $msg;
            }
        }

        if (!headers_sent()) {
            header('Location: '.$url);
        } else {
            echo "<meta http-equiv='Refresh' content='0; URL=$url'>";
        }
        exit;
    }

    /**
     * 301 Moved Permanently
     *
     * @param  string $url
     * @return void
     */
    public static function redirectPermanently($url)
    {
        http_response_code(301);
        header('Location: '.$url);
        exit;
    }

    public static function getRealIP()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    public static function setUri($uri)
    {
        $_SERVER["REQUEST_URI"] = $uri;
        return true;
    }

    public static function getUri()
    {
        return $_SERVER["REQUEST_URI"];
    }

    public static function getBaseUrl()
    {
        $base_url  = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? "https://" : "http://";
        $base_url .= $_SERVER["SERVER_NAME"];
        $base_url .= ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);

        return $base_url;
    }

    public static function getCurrentUrl()
    {
        return self::getBaseUrl().$_SERVER["REQUEST_URI"];
    }

    /**
     * Generate absolute url according to relative one
     *
     * @param  string $relative
     * @return string
     */
    public static function urlTo($relative)
    {
        return Option::get('site_url').$relative;
    }

    public static function abort($code, $msg = "Something happened.", $is_json = false)
    {
        http_response_code((int)$code);
        if ($is_json) {
            View::json($msg, $code);
        } else {
            $config = require BASE_DIR."/config/view.php";
            if (View::exists("errors.$code")) {
                echo View::make('errors.'.$code)->with('code', $code)->with('message', $msg);
            } else {
                echo View::make('errors.e')->with('code', $code)->with('message', $msg);
            }
            exit;
        }
    }
}
