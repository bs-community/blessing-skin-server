<?php

namespace App\Providers;

use Validator;
use Illuminate\Support\ServiceProvider;

class ValidatorExtendServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        /**
         * @param $a     attribute
         * @param $value value
         * @param $p     parameters
         * @param $v     validator
         */
        Validator::extend('username', function($a, $value, $p, $v) {
            return preg_match("/^([A-Za-z0-9\x{4e00}-\x{9fa5}_]+)$/u", $value);
        });

        Validator::extend('nickname', function($a, $value, $p, $v) {
            return $value == addslashes(trim($value));
        });

        Validator::extend('no_special_chars', function($a, $value, $p, $v) {
            return $value == addslashes(trim($value));
        });

        Validator::extend('playername', function($a, $value, $p, $v) {
            return preg_match("/^([A-Za-z0-9_]+)$/", $value);
        });

        Validator::extend('pname_chinese', function($a, $value, $p, $v) {
            return preg_match("/^([A-Za-z0-9\x{4e00}-\x{9fa5}_]+)$/u", $value);
        });

        Validator::extend('preference', function($a, $value, $p, $v) {
            return preg_match("/^(default|slim)$/", $value);
        });

        Validator::extend('model', function($a, $value, $p, $v) {
            return preg_match("/^(steve|alex|cape)$/", $value);
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
