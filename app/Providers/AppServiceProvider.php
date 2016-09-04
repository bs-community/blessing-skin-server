<?php

namespace App\Providers;

use View;
use Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        View::addExtension('tpl', 'blade');

        Validator::extend('username', function($attribute, $value, $parameters, $validator) {
            return preg_match("/^([A-Za-z0-9\x{4e00}-\x{9fa5}_]+)$/u", $value);
        });

        Validator::extend('nickname', function($attribute, $value, $parameters, $validator) {
            return $value == addslashes(trim($value));
        });

        Validator::extend('no_special_chars', function($attribute, $value, $parameters, $validator) {
            return $value == addslashes(trim($value));
        });

        Validator::extend('playername', function($attribute, $value, $parameters, $validator) {
            return preg_match("/^([A-Za-z0-9_]+)$/", $value);
        });

        Validator::extend('pname_chinese', function($attribute, $value, $parameters, $validator) {
            return preg_match("/^([A-Za-z0-9\x{4e00}-\x{9fa5}_]+)$/u", $value);
        });

        Validator::extend('preference', function($attribute, $value, $parameters, $validator) {
            return preg_match("/^(default|slim)$/", $value);
        });

        Validator::extend('model', function($attribute, $value, $parameters, $validator) {
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
        $this->app->singleton('database', \App\Services\Database\Database::class);
        $this->app->singleton('option', \App\Services\OptionRepository::class);
    }
}
