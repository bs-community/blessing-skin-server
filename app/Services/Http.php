<?php

namespace App\Services;

class Http
{
    /**
     * HTTP redirect
     *
     * @param  string $url
     * @return null
     */
    public static function redirect($url, $msg = "", $use_js = false)
    {
        if ($msg != "") $_SESSION['msg'] = $msg;

        if ($use_js)
            echo "<script>window.location = '$url';</script>";
        else
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

    public static function abort($code, $msg = "Something happened.", $is_json = false)
    {
        http_response_code((int)$code);
        if ($is_json) {
            View::json($msg, $code);
        } else {
            $config = require BASE_DIR."/config/view.php";
            if (file_exists($config['view_path']."/errors/".$code.".tpl"))
                echo View::make('errors.'.$code)->with('code', $code)->with('message', $msg);
            else
                echo View::make('errors.exception')->with('code', $this->code)->with('message', $this->message);
            exit;
        }
    }
}
