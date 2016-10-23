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
        // replace HTTP_HOST with site url setted in options to prevent CDN source problems
        preg_match('/https?:\/\/([^\/]+)\/?.*/', option('site_url'), $host);

        if (!option('auto_detect_asset_url')) {
            // check if host is valid
            if (isset($host[1]) && '' === preg_replace('/(?:^\[)?[a-zA-Z0-9-:\]_]+\.?/', '', $host[1])) {
                $this->app['request']->headers->set('host', $host[1]);
            };
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('database', \App\Services\Database\Database::class);
        $this->app->singleton('option', \App\Services\Repositories\OptionRepository::class);
        // register default cipher
        $this->app->singleton('cipher', "App\Services\Cipher\\".config('secure.cipher'));

        $this->app->singleton('users', \App\Services\Repositories\UserRepository::class);
    }
}
