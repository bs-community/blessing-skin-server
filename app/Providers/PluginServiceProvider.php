<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class PluginServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('plugins', 'App\Services\PluginManager');

        $bootstrappers = $this->app->make('plugins')->getEnabledBootstrappers();

        foreach ($bootstrappers as $file) {
            // bootstraper is a closure
            $bootstrapper = require $file;

            $this->app->call($bootstrapper);
        }
    }
}
