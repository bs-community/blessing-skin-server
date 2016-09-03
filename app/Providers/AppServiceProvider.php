<?php

namespace App\Providers;

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
        \View::addExtension('tpl', 'blade');

        require_once BASE_DIR."/app/helpers.php";

        Validator::extend('username', function($attribute, $value, $parameters, $validator) {
            return preg_match("/^([A-Za-z0-9\x{4e00}-\x{9fa5}_]+)$/u", $value);
        });

        Validator::extend('nickname', function($attribute, $value, $parameters, $validator) {
            return $value == addslashes(trim($value));
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
