<?php

namespace App\Http\Middleware;

use Closure;
use Session;
use Illuminate\Cookie\Middleware\EncryptCookies as BaseEncrypter;

class EncryptCookies extends BaseEncrypter
{
    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array
     */
    protected $except = [
        'locale'
    ];

    public function handle($request, Closure $next)
    {
        if (isset($_COOKIE['uid']) && isset($_COOKIE['token'])) {
            Session::put('uid'  , $_COOKIE['uid']);
            Session::put('token', $_COOKIE['token']);
        }

        return parent::handle($request, $next);
    }
}
