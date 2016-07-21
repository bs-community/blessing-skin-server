<?php

namespace App\Middlewares;

use \Pecee\Http\Middleware\IMiddleware;
use \Pecee\Http\Request;

class RemoveUrlSlashMiddleware implements IMiddleware
{
    public function handle(Request $request)
    {
        /**
         * URL ends with slash will cause many reference problems
         * so I deal it globally in this middleware :)
         */
        if ($_SERVER["REQUEST_URI"] != "/" && substr($_SERVER["REQUEST_URI"], -1) == "/")
        {
            $url  = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? "https://" : "http://";
            $url .= $_SERVER["SERVER_NAME"];
            $url .= ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
            $url .= substr($_SERVER["REQUEST_URI"], 0, -1);

            \Http::redirect($url);
        }
    }
}
